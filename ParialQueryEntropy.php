<?php
// Class ParialQueryEntropy wants to calculate entropy of the query prefix.
// example: "mac ap" can be completed as "mac apps", "mac apple store", 
// "mac apps development code". so the "mac ap" may have a big entropy 
// if the candidate querys are appears with the about freq.

// Class PartialQueryPairfliter wants read the pair_nqq.txt.
// it output the high entropy queries to one file and the low freq to another file
// the entropy may depend on the length of partial query  
 
require_once(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class ParialQueryEntropy{
	function __construct() {
	}
	function GetEntropy($queryTB, $sPrefix) { // sPrefix = safe query prefix
		$sql = sprintf(
			"select `Query`, `NumOfQuery` from `%s` 
			where `Query` like '%s%%' and `NumOfQuery` > 0
			group by `Query`
			",
			$queryTB, $sPrefix
		);
		
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$sum = 0;
		$qs = array();
		while ($row = mysql_fetch_row($result)){
			$qs[$row[0]] = doubleval($row[1]); 
			$sum += intval($row[1]);
		}
		if ($sum == 0){
			return -1.0;
		}
		$entropy = 0.0;
		foreach ($qs as $q => $v){
			$p = $v / $sum;
			$entropy -= $p * log($p,2);  
		}
		return $entropy;
	}
	public static function test() {
		$obj = new ParialQueryEntropy();
		$e = $obj->GetEntropy("QueryCluster_5_Clean", "w");
		echo $e."\n";
		$e = $obj->GetEntropy("QueryCluster_5_Clean", "wa");
		echo $e."\n";
		$e = $obj->GetEntropy("QueryCluster_5_Clean", "wal");
		echo $e."\n";
		$e = $obj->GetEntropy("QueryCluster_5_Clean", "walm");
		echo $e."\n";
		
	}
}

class PartialQueryPairFilter{
	public $t; // term_number
	public $c; // character
	public $entropy; // entropy obj
	public $queryTB;
	public function __construct($queryTB) {
		//default;
		$this->SetTermCharacter(0, 1);
		$this->entropy = new ParialQueryEntropy();
		$this->queryTB = $queryTB;
	}
	public function SetTermCharacter($term_number, $character){
		$this->t = $term_number;
		$this->c = $character;
	}
	public function Filter($fileIn, $fileHout, $fileLout, $threshold) {
		$fpin = fopen($fileIn, "r");
		$fpHout = fopen($fileHout, "w");
		$fpLout = fopen($fileLout, "w");
		while ($pair = fgets($fpin)){
			$pair = trim($pair);
			$pair_array = explode("\t",$pair);
			$num = intval($pair_array[0]); // the number of pair appears
			//$f_query = trim($pair_array[1]); // q1 first query -- ignore
			$next_query = trim($pair_array[2]); // q2 next query
			$next_term_array = explode(" ",$next_query);
		
			if($this->t >= count($next_term_array)){
				$test_next = $next_query;
			}else{
				$test_next="";
				for($i=0; $i< $this->t ;$i++){
					$test_next = $test_next." ".$next_term_array[$i];
				}
				$test_next = trim($test_next);
				$test_next = $test_next." ".substr($next_term_array[$this->t],0,$this->c);
				$test_next = trim($test_next);
			}
			$e = $this->entropy->GetEntropy($this->queryTB, $test_next);
			if ($e >= 0.0 && $e <= $threshold){
				fprintf($fpLout, "%s\n", $pair);
			}else{
				// high entropy or unseen
				fprintf($fpHout, "%s\n", $pair);
			}
		}
		fclose($fpHout);
		fclose($fpLout);
		fclose($fpin);
	}
	public static function test() {
		$obj = new PartialQueryPairFilter("QueryCluster_5_Clean");
		$obj->SetTermCharacter(1, 1);
		$obj->Filter("chuhancheng/Aol_pair_nqq2/Aol_pair_nqq_100.txt", "hight.txt", "low.txt", 5);
	}
}
//ParialQueryEntropy::test();
?>
