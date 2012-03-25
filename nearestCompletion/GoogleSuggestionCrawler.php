<?php
// the program want to query google and get the recommendation of the original 
// then it will save to db

require_once(dirname(__FILE__)."/../connection.php");
require_once(dirname(__FILE__)."/../QueryGoogle.php");
require_once(dirname(__FILE__)."/../simple_html_dom.php");

mysql_select_db($database_cnn,$b95119_cnn);

class GoogleSuggestionCrawler{
	//protected $ub; //upper bound
	//protected $lb; //lower bound
	public $qGoogle;
	//private $tb; //table name
	public function __construct(){
		$this->qGoogle = new QueryGoogle("");
		//$this->ub = $ub;
		//$this->lb = $lb;
		//$this->tb = $tb;
	}
	public function LoadDbAndCrawlRecommendation(){
		//useless
		$sql = sprintf(
			"select `word` from `%s` where `value` >= %d and `value` <= %d order by `value` desc",
			$this->tb, $this->lb, $this->ub
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		while($row = mysql_fetch_row($result)){
			echo $row[0]."\n";
			$this->qGoogle->SetQuery($row[0]);
			$suggestions = $this->qGoogle->Recommendation();
			//print_r($suggestions);
			if (!empty($suggestions)){
				$this->SaveDb($row[0], $suggestions);
			}
			sleep(1);
		}
	}
	public function SaveDb($q, $s){// s for suggestion
		$safeQ1 = addslashes($q);
		foreach ($s as $q2){
			$safeQ2 = addslashes($q2);
			$sql = sprintf(
				"insert into `RelatedQuery` (`q1`, `q2`) values ('%s', '%s')",
				$safeQ1, $safeQ2
			);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
	}
	public function QueryTbToHtml($upperBound,$lowerBound, $table,$savePath){
		$sql = sprintf(
			"select `word`, `rowID` from `%s` where `value` >= %d and `value` <= %d order by `value` desc",
			$table, $lowerBound, $upperBound
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		while($row = mysql_fetch_row($result)){
			echo $row[0]."\n";
			$this->qGoogle->SetQuery($row[0]);
			$html = $this->qGoogle->QueryGooglePage();
			$fp = fopen($savePath."/".$row[1].".html", "w");
			if ($fp == null){
				fprintf(STDERR, $savePath."/".$row[1]."html can't be open\n");
			}else{
				fprintf($fp, "%s", $html->outertext);
				fclose($fp);
			}
			sleep(1);
		}
	}
	public function QueryFileToHtml($qFile, $savePath){
		$fp = fopen($qFile, "r");
		if ($fp == null){
			fprintf(STDERR, "%s can't be opened\n", $qFile);
			return null;
		}
		$pattern = "/\t/";
		while($line = fgets($fp)){
			$line = trim($line);
			if ( empty($line) ){
				continue;
			}
			$list = preg_split($pattern, $line);
			if (count($list) > 2){
				fprintf(STDERR, "contain one more 'tab':%s\n", $line);
				continue;
			}
			$id = $list[0];
			$q = $list[1];
			
			echo $id."\t".$q."\n";
			
			$this->qGoogle->SetQuery($q);
			$html = $this->qGoogle->QueryGooglePage();
			$fpw = fopen($savePath."/".$id.".html", "w");
			if ($fpw == null){
				fprintf(STDERR, $savePath."/".$id.".html can't be open\n");
			}else{
				fprintf($fpw, "%s", $html->outertext);
				fclose($fpw);
			}
			sleep(1);
			
		}	
	}
	public function HtmlFileRecommendationToDb($file){
		//$suggestion = array();
		$html = file_get_html($file);
		$ret = $this->qGoogle->Recommendation($html);
		if ($ret == null){
			//fprintf(STDERR, "%s wrong formate\n", $file);
		}else{
			$this->SaveDb($ret["query"], $ret["suggestion"]);
		}
	}
	public function PathFileRecommendationToDb($dir){
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($filename = readdir($dh)) !== false) {
					$filePath = $dir. "/". $filename;
					if ( filetype($filePath) == "file"){
						$this->HtmlFileRecommendationToDb($filePath);
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
		$obj = new GoogleSuggestionCrawler(6323 , 6323, "Aol_SingleQ");
		//$obj->LoadDb();
		$obj->PathFileRecommendationToDb("./googleHtml_1001up/");
		//$obj->qGoogle->SetQuery("mapquest com");
		//$obj->qGoogle->DumpHtml();
	}
}

class SecondTier{
	public function SelectUnexpanseQ2($tb){
		$sql = sprintf(
			"select distinct(`q1`) from `%s`", $tb
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$q1 = array();
		while($row = mysql_fetch_row($result)){
			$q1[$row[0]] = true;
		}
		
		$sql = sprintf(
			"select distinct(`q2`) from `%s`", $tb
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$unq2 = array();
		while($row = mysql_fetch_row($result)){
			if ( !isset($q1[$row[0]]) ){
				$unq2[] = $row[0];
			}
		}
		
		return $unq2;
	}
	public function DumpUnexpanseQ2($outfile) {
		$unq2 = $this->SelectUnexpanseQ2("RelatedQuery");
		$num = ceil( count($unq2) / 10000.0 );
		for ($i = 0;$i < $num ;$i++){
			$filename = sprintf("%s.%d", $outfile, $i);
			$fp[$i] = fopen($filename, "w");
			if ($fp[$i] == null){
				fprintf(STDERR, $filename ." can't be open\n");
				return;
			}
		}
		foreach ($unq2 as $i => $q2){
			//if ($i % 10000 == 0){
				
			//}
			$fpo = $fp[ floor($i / 10000.0) ];
			fprintf($fpo, "%d\t%s\n", $i, $q2);
		}
		for ($i = 0;$i < $num ;$i++){
			fclose($fp[$i]);
		}
	}
	public static function test(){
		$obj = new SecondTier();
		$obj->DumpUnexpanseQ2("unexpanseQ2");
	}
}
//GoogleSuggestionCrawler::test();
SecondTier::test();
?>
