<?php
// the program want to query google and get the recommendation of the original 
// then it will save to db

require_once(dirname(__FILE__)."/../connection.php");
require_once(dirname(__FILE__)."/../QueryGoogle.php");
require_once(dirname(__FILE__)."/../simple_html_dom.php");

mysql_select_db($database_cnn,$b95119_cnn);

class GoogleSuggestionCrawler{
	protected $ub; //upper bound
	protected $lb; //lower bound
	public $qGoogle;
	private $tb; //table name
	public function __construct($ub, $lb, $tb){
		$this->qGoogle = new QueryGoogle("");
		$this->ub = $ub;
		$this->lb = $lb;
		$this->tb = $tb;
	}
	public function LoadDbAndCrawlRecommendation(){
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
	public function SaveHtmlOnly($path){
		$sql = sprintf(
			"select `word`, `rowID` from `%s` where `value` >= %d and `value` <= %d order by `value` desc",
			$this->tb, $this->lb, $this->ub
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		while($row = mysql_fetch_row($result)){
			echo $row[0]."\n";
			$this->qGoogle->SetQuery($row[0]);
			$html = $this->qGoogle->QueryGooglePage();
			$fp = fopen($path."/".$row[1].".html", "w");
			if ($fp == null){
				fprintf(STDERR, $path."/".$row[1]."html can't be open\n");
			}else{
				fprintf($fp, "%s", $html->outertext);
				fclose($fp);
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

//GoogleSuggestionCrawler::test();

?>
