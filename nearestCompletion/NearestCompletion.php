<?php
// Class QueryCompletion wants to complete the query by concept matching.
// with entropy

require_once(dirname(__FILE__)."/../connection.php");

mysql_select_db($database_cnn,$b95119_cnn);

class NearestCompletion{
	public $vectorTB;
	public $q1;// safe q
	public $q2;// safe q
	public function __construct($q1, $q2, $vTB)
	{
		$this->q1 = addslashes($q1);
		$this->q2 = addslashes($q2);
		$this->vectorTB = $vTB;
	}
	public function GetCompletion(){
		// all strings read from db maybe unsafe 
		$sql = sprintf(
			"select `ngram`, `value` from `%s` 
			where `query` = '%s'", $this->vectorTB, $this->q1);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$v1 = array();
		while($row = mysql_fetch_row($result)){
			$ngram = addslashes($row[0]);
			$v1[$ngram] = doubleval($row[1]);
		}
		
		$sql = sprintf(
			"select `query`, `ngram`, `value` from `%s` 
			where `query` like '%s%%'", $this->vectorTB, $this->q2);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$v2s = array();
		while($row = mysql_fetch_row($result)){
			$q = addslashes($row[0]);
			$ngram = addslashes($row[1]);
			$v2s[$q][$ngram] = doubleval($row[2]);
		}
		foreach ($v2s as $q => $v2){
			$sim[$q] = $this->similarity($v1, $v2);
		}
		arsort($sim);
		return $sim;
	}
	public function similarity($v1, $v2){
		$sum = 0.0;
		foreach($v1 as $ngram => $value){
			if ( isset($v2[$ngram]) ){
				$sum += $value * $v2[$ngram];
			}
		}
		return $sum;
	}
	public static function test(){
		$obj = new NearestCompletion("aaa", "a", "AolNgramVector");
		$ret = $obj->GetCompletion();
		print_r($ret);
		$obj = new NearestCompletion("youtube", "a", "AolNgramVector");
		$ret = $obj->GetCompletion();
		print_r($ret);
	}
}
NearestCompletion::test();
?>
