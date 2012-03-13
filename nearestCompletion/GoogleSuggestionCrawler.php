<?php
// the program want to query google and get the recommendation of the original 
// then it will save to db

require_once(dirname(__FILE__)."/../connection.php");
require_once(dirname(__FILE__)."/../QueryGoogle.php");

mysql_select_db($database_cnn,$b95119_cnn);

class GoogleSuggestionCrawler{
	protected $ub; //upper bound
	protected $lb; //lower bound
	protected $qGoogle;
	private $tb; //table name
	public function __construct($ub, $lb, $tb){
		$this->qGoogle = new QueryGoogle("");
		$this->ub = $ub;
		$this->lb = $lb;
		$this->tb = $tb;
	}
	public function LoadDb(){
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
			$this->SaveDb($row[0], $suggestions);
			sleep(1);
		}
	}
	public function SaveDb($q, $s){// s for suggestion
		foreach ($s as $q2){
			$sql = sprintf(
				"insert into `RelatedQuery` (`q1`, `q2`) values ('%s', '%s')",
				$q, $q2
			);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
	}
	public static function test(){
		$obj = new GoogleSuggestionCrawler(160000 , 60000, "Aol_SingleQ");
		$obj->LoadDb();
	}
}

//GoogleSuggestionCrawler::test();

?>