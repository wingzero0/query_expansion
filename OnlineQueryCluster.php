<?php
// Class OnlineQueryClassify want to classify the query into the class of concept 
// that we trained offline.
// The query may be seen or unseen from log
// If the query is seen, I will select it directly
// else if the query is unseen, I will classify it online with wiki documents or 
// some other corpus
//
// if the query is in more than one concept, 
// it will output the concept in prob decreasing order 

require_once(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);


class OnlineQueryClassify{
	public $targetTB;
	public $query;
	public function __construct($TB = "QueryCluster"){
		$this->targetTB = $TB;
	}
	public function GetConcept($query){
		$this->query = $query;
		$ret = $this->CheckInDB();
		if ($ret != NULL){
			return $ret;
		}
		$ret = $this->OnlineClassify();
		return $ret;
	}
	public function CheckInDB(){
		// The index of return array is the claster number. 
		// The corresponding value is the SimValue  
		$sql = sprintf(
			"select `ClusterNum`, `SimValue` from `%s`
			where `Query` = '%s' order by `SimValue` desc", 
			$this->targetTB, $this->query);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = NULL;
		while($row = mysql_fetch_row($result)){
			$clusterS[intval($row[0])] = doubleval($row[1]);
		}
		return $clusterS;
	}
	public function OnlineClassify(){
		// non-complete
		return NULL;
	}
	public static function test(){
		$obj = new OnlineQueryClassify();
		$ret = $obj->GetConcept("haha");
		print_r($ret);
	}
}
OnlineQueryClassify::test();
?>
