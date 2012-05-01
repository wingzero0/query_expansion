<?php
// the program want to query google and get the snippy 
// then it will save to db

require_once(dirname(__FILE__)."/../connection.php");
require_once(dirname(__FILE__)."/../QueryGoogle.php");

mysql_select_db($database_cnn,$b95119_cnn);

class GoogleSnippyVector{
	//protected $ub; //upper bound
	//protected $lb; //lower bound
	public $qGoogle;
	//private $tb; //table name
	public function __construct(){
		$this->qGoogle = new QueryGoogle("");
	}
	public function PathFileHtmlToSnippy($dir){
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($filename = readdir($dh)) !== false) {
					$filePath = $dir. "/". $filename;
					if ( filetype($filePath) == "file"){
						$snippy = $this->HtmlFileToSnippyTerm($filePath);
						if ($snippy != null){
							$this->SaveTfToDb($snippy["query"], $snippy["snippyTerm"]);// they are unique gram
						}
					}
				}
				closedir($dh);
			}else{
				fprintf(STDERR, "%s can't not open\n", $dir);
			}
		}else{
			fprintf(STDERR, "%s is not dir\n", $dir);
		}
	}
	public function HtmlFileToSnippyTerm($file){
		$html = file_get_html($file);
		$s = $this->qGoogle->Snippy($html);
		if ($s == null){
			return null;
		}
		$dict = $this->qGoogle->SnippyVector($s["snippy"]);
		$normalize = $this->TfNormalize($dict);
		$ret["query"] = $s["query"];
		$ret["snippyTerm"] = $normalize; // the snippy change into vector form. 
		return $ret;
	}
	public function TfNormalize($data) {
		$normalize = array();
		$max = 0;
		foreach($data as $n){
			if ($max < $n){
				$max = $n;
			}
		}
		foreach($data as $q => $n){
			$normalize[$q] = (double) $n / (double) $max;
		}
		return $normalize;
	}
	public function SaveTfToDb($q, $ngrams){
		$safeQ = addslashes($q);
		foreach ($ngrams as $ngram => $v){
			$safeNgram = addslashes($ngram);
			$sql = sprintf(
				"insert into `SnippyTf` (`query`, `ngram`, `value`) values ('%s', '%s', '%lf')",
				$safeQ, $safeNgram, $v
			);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
	}
	public function GetTfFromDb($db){
		$sql = sprintf(
			"select `query`, `ngram`, `value` from `%s`", $db
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$tf = array();
		while ( $row = mysql_fetch_row($result) ){
			$tf[$row[0]][$row[1]] = doubleval($row[2]);
		}
		return $tf;
	}
	public function IdfFromDB($db){
		$sql = sprintf(
			"select `query`, count(`query`) from `%s` group by `query`", $db
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$totalQueryNum = mysql_num_rows($result);
		
		$sql = sprintf(
			"select `ngram`, count(`ngram`) from `%s` group by `ngram`", $db
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		
		$idf = array();
		while ( $row = mysql_fetch_row($result) ){
			$idf[$row[0]] = log((double) $totalQueryNum / (double) $row[1]); 
		}
		ksort($idf);
		return $idf;
	}
	public function SaveTfIdfVector($tf, $idf) {
		foreach($tf as $q => $ngrams){
			$safeQ = addslashes($q);
			foreach($ngrams as $ngram => $value){
				$safeNgram = addslashes($ngram);
				if ( isset($idf[$ngram]) ){
					$weight = $value * $idf[$ngram];
					$sql = sprintf(
						"insert into `SnippyVector` (`query`, `ngram`, `value`) values ('%s', '%s', '%lf')",
						$safeQ, $safeNgram, $weight
					);
					$result = mysql_query($sql) or die($sql."\n".mysql_error());
				}else{
					fprintf(STDERR, "ngram:%s not in idf\n", $ngram);
				}
			}
		}
	}
	public static function test(){
		$obj = new GoogleSnippyVector();
		//$obj->PathFileHtmlToSnippy("./googleHtml_1001up/");
		$db = "SnippyTf";
		fprintf(STDERR, "select tf\n");
		$tf = $obj->GetTfFromDb($db);
		fprintf(STDERR, "select idf\n");
		$idf = $obj->IdfFromDB($db);
		fprintf(STDERR, "inserting vector\n");
		$obj->SaveTfIdfVector($tf, $idf); 
		//$obj->qGoogle->SetQuery("mapquest com");
		//$obj->qGoogle->DumpHtml();
	}
}
GoogleSnippyVector::test();

?>
