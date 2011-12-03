<?php
// Class QueryCompletion wants to complete the query by concept matching.

require_once(dirname(__FILE__)."/QuerySpliter.php");
require_once(dirname(__FILE__)."/OnlineQueryClassify.php");
require_once(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class QueryCompletion{
	public $wordTB;
	public $queryTB;
	public $clusterFlowTB;
	public $q1;
	public $q2;
	public $queryClassifier;
	public $threshold;
	public $querySpliter;
	public function __construct($q1, $q2, $qTB, $wTB,$cFlowTB){
		$this->q1 = $q1;
		$this->q2 = $q2;
		$this->queryClassifier = new OnlineQueryClassify($qTB);
		$this->wordTB = $wTB;
		$this->clusterFlowTB = $cFlowTB;
		$this->threshold = 0.5;
		$this->querySpliter = new QuerySpliter("");
	}
	public function GetQueryCompletionPool() {
		$q1Concepts = $this->queryClassifier->GetConcept($this->q1);
		// the return value of GetConcept is an array of concept prob.
		// the index of the array is concept number, 
		// the value of the array is prob of q1 being in that concept
		print_r($q1Concepts);
		$conceptPool = array();
		foreach ($q1Concepts as $c1 => $probC1){
			$flowProb = $this->GetConceptFlowProb($c1);
			print_r($flowProb);
			foreach ($flowProb as $c2 => $prob){
				$prob2 = $this->QueryGeneratingProb($c2, $this->q2);
				if ($prob * $prob2 > $this->threshold){
					$conceptPool[$c1][$c2] = $prob * $prob2; 
				}
			}
		}
		
		return $conceptPool;
	}
	public function QueryGeneratingProb($c, $query){
		$this->querySpliter->ReplaceNewQuery($query);
		$words = $this->querySpliter->SplitTerm();
		return 0.0;
	}
	public function GetConceptFlowProb($c1) {
		// return a list of prob that start from c1
		// the index of the array is cluster2's number, 
		// the value of the array is prob of the corresponding flow
		
		$sql = sprintf(
			"select `Cluster2`,`Prob` from `%s`
			where `Cluster1` = %d 
			order by `Prob` desc", 
			$this->clusterFlowTB, $c1);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = NULL;
		while($row = mysql_fetch_row($result)){
			$clusterS[intval($row[0])] = doubleval($row[1]);
		}
		return $clusterS;
	}
	public function GetWordInConcept($clusterNum, $prefix){
		// return a list of words start the input prefix
		$sql = sprintf(
			"select `Word`,`NumOfWord` from `%s`
			where `ClusterNum` = %d and `Word` like '%s%%' 
			order by `NumOfWord` desc", 
			$this->wordTB, $clusterNum, $prefix);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = NULL;
		while($row = mysql_fetch_row($result)){
			$clusterS[$row[0]] = intval($row[1]);
		}
		return $clusterS;
	}
	public static function test(){
		$obj = new QueryCompletion("haha", "schwab", 
			"QueryClusterTest", "WordClusterTest", "ClusterFlowProb");
		$ret = $obj->GetQueryCompletionPool();
		print_r($ret);
		$ret = $obj->GetWordInConcept(1, "sw");
		print_r($ret);
	}
}
QueryCompletion::test();
?>
