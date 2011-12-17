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
	public $flowThreshold;
	public $querySpliter;
	public $nGenerate;
	public function __construct($q1, $q2, $qTB, $wTB,$cFlowTB){
		$this->q1 = addslashes($q1);
		$this->q2 = addslashes($q2);
		$this->queryClassifier = new OnlineQueryClassify($qTB);
		$this->queryTB = $qTB;
		$this->wordTB = $wTB;
		$this->clusterFlowTB = $cFlowTB;
		//$this->threshold = 0.00001;
		$this->threshold = 0.0;
		$this->flowThreshold = 0.01;
		$this->querySpliter = new QuerySpliter($q2);
		$this->nGenerate = new NgramGenerate($q2);
	}
	public function GetQueryConceptPool() {
		$q1Concepts = $this->queryClassifier->GetConcept($this->q1);
		// the return value of GetConcept is an array of concept prob.
		// the index of the array is concept number, 
		// the value of the array is prob of q1 being in that concept
		
		// Get the pool of concept
		$conceptPool = array();
		foreach ($q1Concepts as $c1 => $probC1){
			fprintf(STDERR,"processing c1:%d\n", $c1);
			$flowProb = $this->GetConceptFlowProb($c1);
			//print_r($flowProb);
			if (empty($flowProb)){
				continue;
			}
			foreach ($flowProb as $c2 => $prob){
				$prob2 = $this->QueryGeneratingProb($c2, $this->q2);
				echo "c1 = $c1, c2 = $c2 prob1 = $prob prob2 = $prob2\n";
				if ($prob * $prob2 > $this->threshold){
					//$conceptPool[$c1][$c2] = $probC1 * $prob * $prob2; 
					$conceptPool[$c1][$c2] = $prob * $prob2; // ignore the ProbC1 in the first version
				}
			}
			if (isset($conceptPool[$c1])){
				arsort($conceptPool[$c1]);
			}else{
				fprintf(STDERR,"the c1 following is empty\n");
			}
		}
		//arsort($conceptPool[$c1]);	
		return $conceptPool;
	}
	public function QueryGeneratingProb($c, $query){
		//$this->querySpliter->ReplaceNewQuery($query);
		//$words = $this->querySpliter->SplitTerm();
		
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
				fprintf(STDERR, "the number of query in cluster is empty\n");
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
			where `Cluster1` = %d and `prob` > %lf 
			order by `Prob` desc", 
			$this->clusterFlowTB, $c1,$this->flowThreshold);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = NULL;
		while($row = mysql_fetch_row($result)){
			$clusterS[intval($row[0])] = doubleval($row[1]);
		}
		return $clusterS;
	}
	public function GetQueryCombination(){
		$words = $this->querySpliter->SplitTerm();
		$conceptPool = $this->GetQueryConceptPool();
		//print_r($words);		
		$orignalWords = "";
		if (isset($words["word"][0])){
			$orignalWords = $words["word"][0];
			for ($i = 1 ;$i<count($words["word"]);  $i++){
				$orignalWords.= " ".$words["word"][$i];
			}
		}
		$queryPool = array();
		$uniqueC2 = array();
		if (empty($conceptPool)){
			fprintf(STDERR,"conceptPool is empty\n");
			return -1;
		}
		foreach ($conceptPool as $c1 => $c2Set){
			foreach ($c2Set as $c2 => $prob){
				$uniqueC2[$c2] = 1;
			}
		}
		foreach ($uniqueC2 as $c2 => $value){
			//$newWords = $this->GetWordInConcept($c2, $words["partial"]);
			$newWords = $this->GetWordPhraseInConcept($c2, $words["partial"]);
			//the new words may be duplicate in differnt c2;
			if ( empty($newWords) ){
				continue;
			}
			
			$whiteSpaces = "\s{2,}";// two or more spaces
			foreach($newWords as $newWord){
				$tmpQuery = mb_ereg_replace($whiteSpaces, " ", $orignalWords." ".$newWord);
				$newQuery = mb_ereg_replace("^(\s+)", "", $tmpQuery);
				//echo $newQuery."\n";
				$prob = $this->QueryGeneratingProb($c2, $newQuery);
				$queryPool[$c2][$newQuery] = $prob;
			}
			arsort($queryPool[$c2]);
		}
		
		// the sorting prob above may be wrong.
		// because that it only consider the final term of the prob chain.
		// if we need to calculate the precise prob, we should consider 
		// the prob of concept pool
		return $queryPool;
	}
	public function GetWordInConcept($clusterNum, $prefix){
		// return a list of words start the input prefix
		$sql = sprintf(
			"select `Word`,`NumOfWord` from `%s`
			where `ClusterNum` = %d and `Word` like '%s%%' 
			order by `NumOfWord` desc", 
			$this->wordTB, $clusterNum, $prefix);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = array();
		while($row = mysql_fetch_row($result)){
			$clusterS[$row[0]] = intval($row[1]);
		}
		return $clusterS;
	}
	public function GetWordPhraseInConcept($clusterNum,$wordPrefix){
		// return a list of phrases start with the input word
		$sql = sprintf(
			"select `Query` from `%s`
			where `ClusterNum` = %d and 
			(`Query` like '%s%%' OR `Query` like '%% %s%%') 
			", 
			$this->queryTB, $clusterNum, $wordPrefix, $wordPrefix);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = array();
		$pattern = sprintf("(^%s| %s)(.*)", $wordPrefix,$wordPrefix);
		while($row = mysql_fetch_row($result)){
			//$clusterS[$row[0]] = intval($row[1]);
			
			$ret = mb_eregi($pattern, $row[0], $matches);
			//$ret = mb_ereg($pattern, $row[0], $matches);
			if ($ret === false){
				print_r($row[0]);
			}else {
				$clusterS[] = addslashes($matches[0]);
			}
		}
		return $clusterS;
	}	
	public static function test(){
		$obj = new QueryCompletion("haha", "schwab ha文 s", 
			"QueryClusterTest", "WordClusterTest", "ClusterFlowProb");
		$ret = $obj->GetQueryCombination();
		print_r($ret);
		$obj = new QueryCompletion("haha", "schwab ha文 su", 
			"QueryClusterTest", "WordClusterTest", "ClusterFlowProb");
		$ret = $obj->GetQueryCombination();
		print_r($ret);
		
		//$ret = $obj->GetWordInConcept(1, "sw");
		//print_r($ret);
	}
}
//QueryCompletion::test();
?>
