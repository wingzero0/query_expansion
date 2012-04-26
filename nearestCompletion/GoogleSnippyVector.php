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
	public function SaveDb($q, $ngrams){
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
	public function HtmlFileToSnippyTerm($file){
		$html = file_get_html($file);
		$s = $this->qGoogle->Snippy($html);
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
	public function PathFileHtmlToSnippy($dir){
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($filename = readdir($dh)) !== false) {
					$filePath = $dir. "/". $filename;
					if ( filetype($filePath) == "file"){
						$snippy = $this->HtmlFileToSnippyTerm($filePath);
						$this->SaveDb($snippy["query"], $snippy["snippyTerm"]);// they are unique gram
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
	public static function test(){
		$obj = new GoogleSnippyVector();
		$obj->PathFileHtmlToSnippy("./googleHtml_1001up/");
		//$obj->qGoogle->SetQuery("mapquest com");
		//$obj->qGoogle->DumpHtml();
	}
}
//GoogleSnippyVector::test();

?>
