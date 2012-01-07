<?php
// Class QueryConceptTypoFilter want to filter out the typo in concept
// It will load the QueryCluster table.
// If two queries have a short editing distance, 
// then the low frequency query will be drop

require(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);


class QueryConceptTypoFilter {
	public $oTB; // oldTB
	public $nTB; // newTB
	public $clusterQ; // cluster Query / concept Query
	public $querys;
	public $cleanQs;
	public $romovedTypoQ;
	public function __construct($oldTB, $newTB){
		$this->oTB = $oldTB;
		$this->nTB = $newTB;
	}
	public function RemoveLowEditDistance(){
		asort($this->querys);
		$qs = array_keys($this->querys);
		foreach ($this->querys as $q => $v){
			$this->cleanQs[$q] = $v; // copy
		}
		for ($i = 0;$i< count($qs) -1; $i++){ // two different direction
			for ($j = count($qs) - 1;$j >$i; $j--){
				if (levenshtein($qs[$i], $qs[$j]) < 3){
					//$this->querys[$qs[$j]] += $this->querys[$qs[$i]];
					$this->cleanQs[$qs[$j]] += $this->cleanQs[$qs[$i]];
					$translate[$qs[$i]] = $qs[$j]; // $i=> $j
					unset($this->cleanQs[$qs[$i]]);// delete $i
					break;
				}
			}
		}
		echo "translate:\n";
		print_r($translate);
		return $this->cleanQs;
	}
	public function LoadDB(){
		$sql = sprintf(
			"select `Query`, `ClusterNum`, `NumOfQuery`
			from `%s`
			where `ClusterNum` = 61
			",
			$this->oTB
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		while ($row = mysql_fetch_assoc($result)){
			$q = addslashes($row["Query"]);
			$c = intval($row["ClusterNum"]);
			$clicked = intval($row["NumOfQuery"]);
			$this->clusterQ[$c][$q] = $clicked; 
		}
	}
	public function CleanQuery(){
		foreach ($this->clusterQ as $c => $query1){
			$this->querys = $query1;
			$tmp = $this->RemoveLowEditDistance();
			print_r($tmp);
		}
	}
	public static function test(){
		$obj = new QueryConceptTypoFilter("QueryCluster_4", "QueryClusterClean_4");
		$obj->LoadDB();
		$obj->CleanQuery();
	}
}
QueryConceptTypoFilter::test();