<?php
// Class QueryCompletion wants to complete the query by concept matching.

require_once(dirname(__FILE__)."/QuerySpliter.php");
require_once(dirname(__FILE__)."/OnlineQueryClassify.php");
require_once(dirname(__FILE__)."/connection.php");
require_once(dirname(__FILE__)."/NgramGenerate.php");

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
	public $nGenerate;
	public function __construct($q1, $q2, $qTB, $wTB,$cFlowTB){
		$this->q1 = $q1;
		$this->q2 = $q2;
		$this->queryClassifier = new OnlineQueryClassify($qTB);
		$this->queryTB = $qTB;
		$this->wordTB = $wTB;
		$this->clusterFlowTB = $cFlowTB;
		$this->threshold = 0.00001;
		$this->querySpliter = new QuerySpliter("");
		$this->nGenerate = new NgramGenerate($q2);
	}
	public function GetQueryCompletionPool() {// should rename to GetQueryConceptPool
		$q1Concepts = $this->queryClassifier->GetConcept($this->q1);
		// the return value of GetConcept is an array of concept prob.
		// the index of the array is concept number, 
		// the value of the array is prob of q1 being in that concept
		
		// Get the pool of concept
		$conceptPool = array();
		foreach ($q1Concepts as $c1 => $probC1){
			$flowProb = $this->GetConceptFlowProb($c1);
			//print_r($flowProb);
			if (empty($flowProb)){
				continue;
			}
			foreach ($flowProb as $c2 => $prob){
				//echo "c1 = $c1, c2 = $c2\n";
				$prob2 = $this->QueryGeneratingProb($c2, $this->q2);
				if ($prob * $prob2 > $this->threshold){
					//$conceptPool[$c1][$c2] = $probC1 * $prob * $prob2; 
					$conceptPool[$c1][$c2] = $prob * $prob2; // ignore the ProbC1 in the first version
				}
			}
		}
		arsort($conceptPool[$c1]);	
		return $conceptPool;
	}
	public function QueryGeneratingProb($c, $query){
		$this->querySpliter->ReplaceNewQuery($query);
		$words = $this->querySpliter->SplitTerm();
		
		//$nGenerate = new NgramGenerate($query);
		$ret = $this->nGenerate->ReplaceNewQuery($query);
		//echo "c:$c\n";
		$qWords = $this->nGenerate->GetQWords(); // for counting the number of term in NgramGenerate 
		//The number of terms are different between querySpliter and NgramGenerate.
		
		$n = count($qWords);
		$ngrams = $this->nGenerate->GetNgrams($n);
		$ngrams1 = $this->nGenerate->GetNgrams($n -1);
		//print_r($ngrams1);
		$ngrams2 = $this->nGenerate->GetNgrams($n -2);
		//print_r($ngrams2);
		 
		$prob = $this->NgramGeneratingProb($c, $ngrams); // the whole one
		//echo "whole:".$prob."\n";
		if ($ngrams1 != null){
			$prob += $this->NgramGeneratingProb($c, $ngrams1) / 2;
			//echo "plus 1:".$prob."\n";
		}
		if ($ngrams2 != null){
			$prob += $this->NgramGeneratingProb($c, $ngrams2) / 3;
			//echo "plus 2:".$prob."\n";
		}
		return $prob;
	}
	private function NgramGeneratingProb($c, $ngrams){
		// return the summation probability of the ngrams
		$sum = 0;
		foreach ($ngrams as $i => $ngram){
			$sql = sprintf(
				"select sum(`NumOfQuery`) from `%s`
				where `ClusterNum` = %d and `Query` like '%%%s%%'
				group by `ClusterNum`", 
				$this->queryTB, $c, $ngram);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
			if($row = mysql_fetch_row($result)){
				$sum += intval($row[0]);
			}
		}
		
		$sql = sprintf(
			"select sum(`NumOfQuery`) from `%s`
			where `ClusterNum` = %d
			group by `ClusterNum`", 
			$this->queryTB, $c);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		if($row = mysql_fetch_row($result)){
			if (intval($row[0]) == 0){
				return 0.0; // cluster is empty??
			}else{
				//echo "ClusterNum total:".$row[0]."\n";
				//echo "Sum:".$sum."\n";
				$prob = doubleval($sum) / doubleval($row[0]);
				return $prob;
			}
		}else {
			fprintf(STDERR, "DB error\n");
			return -1.0;
		}
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
		$obj = new QueryCompletion("haha", "schwab ha文 sitdown", 
			"QueryClusterTest", "WordClusterTest", "ClusterFlowProb");
		$ret = $obj->GetQueryCompletionPool();
		//print_r($ret);
		//$ret = $obj->GetWordInConcept(1, "sw");
		//print_r($ret);
	}
}
QueryCompletion::test();
?>
