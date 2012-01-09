<?php
// Class QueryCompletionBaseline wants to complete the query by 
// most frequence with considering concept

require_once(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class QueryCompletionBaseline{
	public $wordTB;
	public $queryTB;
	public $threshold;
	public $limit; // return limit
	public function __construct($q1, $q2, $qTB, $wTB,$threshold, $limit = 10){
		$this->q1 = addslashes($q1);
		$this->q2 = addslashes($q2);
		$this->queryTB = $qTB;
		$this->wordTB = $wTB;
		$this->threshold = $threshold;
		$this->limit = $limit;
	}
	public function GetMostFreqQuery(){
		$sql = sprintf(
			"select `Query`, sum(`NumOfQuery`) from `%s`
			where `Query` like '%s%%' 
			and `NumOfQuery` >= %d
			group by `Query`
			order by sum(`NumOfQuery`) desc
			", 
			$this->queryTB, $this->q2, $this->threshold);
		if ($this->limit >=1){
			$sql .= " limit 0, ".$this->limit ;
		}
		$result = mysql_query($sql) or die($sql."\n".mysql_error());

		//echo $sql."\n";
		$completion = array(); 
		while($row = mysql_fetch_row($result)){
			$q = addslashes($row[0]);
			$completion[$q] = intval($row[1]);
		}
		return $completion;

	}	
}
?>
