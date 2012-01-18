<?php
// Class QueryCompletion wants to complete the query by concept matching.

require_once(dirname(__FILE__)."/QuerySpliter.php");
require_once(dirname(__FILE__)."/OnlineQueryClassify.php");
require_once(dirname(__FILE__)."/connection.php");
require_once(dirname(__FILE__)."/NgramGenerate.php");
require_once(dirname(__FILE__)."/QueryGoogle.php");
mysql_select_db($database_cnn,$b95119_cnn);

class QueryCompletion{
	public $wordTB;
	public $queryTB; // relaxed
	public $queryTBTight; // tight 
	public $clusterFlowTB;
	public $llrTB; // llr => t-test
	public $q1;
	public $q2;
	public $queryClassifier;
	//public $threshold; // for QueryConcept and Query Generating Prob (in fuction GetQueryConpetPool)
	// $threshold useless -> should be delete
	public $flowThreshold; // for Query Concept flow Prob
	public $querySpliter;
	public $nGenerate;
	public $alpha; // for Query Generating Prob -- N gram
	public $beta; // for Query Generating Prob -- N - 1 gram
	public $gamma; //for Query Generating Prob -- N - 2 gram
	protected $flowProb;// the max prob from any c1 of q1 to a specify c2
	protected $tValue;
	protected $clusterQuerys;
	protected $clusterSum;
	public function __construct($q1, $q2, $qTB, $qTBTight, $wTB,$cFlowTB,$llrTB,
		$flowThreshold, $threshold, $llrThreshold,
		$alpha, $beta, $gamma)
	{
		$this->q1 = addslashes($q1);
		$this->q2 = addslashes($q2);
		$this->queryClassifier = new OnlineQueryClassify($qTB);
		$this->queryTB = $qTB;
		$this->queryTBTight = $qTBTight;
		$this->wordTB = $wTB;
		$this->clusterFlowTB = $cFlowTB;
		$this->llrTB = $llrTB;
		//$this->threshold = $threshold;//0.0 output everything
		$this->llrThreshold = $llrThreshold;//30.0 may be ok
		$this->flowThreshold = $flowThreshold;//0.01 may be ok
		$this->querySpliter = new QuerySpliter($q2);
		$this->nGenerate = new QuerySpliter($q2);
		$this->alpha = $alpha;
		$this->beta = $beta;
		$this->gamma = $gamma;
		$this->tValue = $this->InitTValue();
		$this->clusterQuerys = $this->InitConceptQuerys();
		$this->clusterSum = $this->InitConceptQuerysCount();
	}
	public function GetQueryConceptPool() {
		$q1Concepts = $this->queryClassifier->GetConcept($this->q1);
		// the return value of GetConcept is an array of concept prob.
		// the index of the array is concept number, 
		// the value of the array is prob of q1 being in that concept

		// Get the pool of concept
		$conceptPool = array();
		$tmpPool = array();
		foreach ($q1Concepts as $c1 => $probC1){
			//fprintf(STDERR,"processing c1:%d\n", $c1);
			$flowProb = $this->GetConceptFlowProb($c1);
			//fprintf(STDERR,"FlowProb amount:%d\n", count($flowProb));
			if (empty($flowProb)){
				continue;
			}
			foreach ($flowProb as $c2 => $prob){
				if ( !isset($this->flowProb[$c2]) || $this->flowProb[$c2] < $prob){
					$this->flowProb[$c2] = $prob; // save the prob in memory
					//this->$flowProb save the max prob from any c1 of q1 to a specify c2
				}
				$prob2 = $this->QueryGeneratingProb($c2, $this->q2);
				if ($prob2 > 0.0){
					$tmpPool[$c1][$c2] = $prob * $prob2; // ignore the ProbC1 in the first version
				}
				//fprintf(STDERR, "c1 = $c1, c2 = $c2 q2 = %s prob1 = $prob prob2 = $prob2\n", $this->q2);
			}
			$limit = 20;
			if ( isset($tmpPool[$c1]) && count($tmpPool[$c1]) > $limit){
				arsort($tmpPool[$c1]);
				$i = 0;
				foreach ($tmpPool[$c1] as $c2 => $v){// take top 10 sense
					$conceptPool[$c1][$c2] = $v;
					$i++;
					if ($i >= $limit){
						break;
					}
				}
			}else if (isset($tmpPool[$c1]) && count($tmpPool[$c1]) <= $limit){
				$conceptPool[$c1] = $tmpPool[$c1];
			}else{
				//fprintf(STDOUT,"the c1 following is empty\n");
			}
			//fprintf(STDERR,"c2 candidate amount:%d\n", count($conceptPool[$c1]));
		}
		return $conceptPool;
	}
	public function QueryGeneratingProb($c, $query){
		$ret = $this->nGenerate->ReplaceNewQuery($query);

		$qWords = $this->nGenerate->GetQWords(); // for counting the number of term in NgramGenerate 
		//The number of terms are different between querySpliter and NgramGenerate.

		$n = count($qWords);
		$ngrams = $this->nGenerate->GetNgrams($n);
		$ngrams1 = $this->nGenerate->GetNgrams($n -1);
		//print_r($ngrams1);
		$ngrams2 = $this->nGenerate->GetNgrams($n -2);
		//print_r($ngrams2);

		$prob = $this->alpha * $this->NgramGeneratingProb($c, $ngrams); // the whole one
		//echo "whole:".$prob."\n";
		if ($ngrams1 != null){
			$prob += $this->beta * $this->NgramGeneratingProb($c, $ngrams1) / 2;
			//echo "plus 1:".$prob."\n";
		}
		if ($ngrams2 != null){
			$prob += $this->gamma * $this->NgramGeneratingProb($c, $ngrams2) / 3;
			//echo "plus 2:".$prob."\n";
		}
		return $prob;
	}
	private function NgramGeneratingProb($c, $ngrams){
		// return the summation probability of the ngrams
		$sum = 0;
		foreach ($ngrams as $i => $ngram){
			if (empty($ngram)){
				continue;
			}
			foreach ($this->clusterQuerys[$c] as $q => $v){
				//echo $q."\n";
				if ( strstr($q, $ngram) !== false ){
					$sum += $v;
				}
			}
		}
		$prob = (double) $sum / (double) $this->clusterSum[$c];
		return $prob;
	}
	public function GetConceptFlowProb($c1) {
		// return a list of prob that start from c1
		// the index of the array is cluster2's number, 
		// the value of the array is prob of the corresponding flow

		$sql = sprintf(
			"select `Cluster2`,`Prob` from `%s`
			where `Cluster1` = %d and `Prob` > %lf 
			order by `Prob` desc
			limit 0, 1000
			", 
			$this->clusterFlowTB, $c1,$this->flowThreshold);
			
			//echo $sql."\n";
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = array();
		while($row = mysql_fetch_row($result)){
			$clusterS[intval($row[0])] = doubleval($row[1]);
		}
		return $clusterS;
	}
	public function GetQueryCombination(){
		$words = $this->querySpliter->SplitTerm();
		$conceptPool = $this->GetQueryConceptPool();
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
			//fprintf(STDERR,"conceptPool is empty\n");
			$emptyArray = array();
			return $emptyArray;//return the empty array()
		}
		foreach ($conceptPool as $c1 => $c2Set){
			foreach ($c2Set as $c2 => $prob){
				if ( !isset($uniqueC2[$c2]) || $prob > $uniqueC2[$c2] ){
					$uniqueC2[$c2] = $prob;
				}
			}
			//print_r($uniqueC2);
		}
		foreach ($uniqueC2 as $c2 => $value){
			// WordInConcept
			$newWords = $this->GetWordInConcept($c2, $words["partial"]);

			// PhraseInConcept 
			//$newWords = $this->GetWordPhraseInConcept($c2, $words["partial"]);
			//the new words may be duplicate in differnt c2;
			if ( empty($newWords) ){
				continue;
			}

			$whiteSpaces = "\s{2,}";// two or more spaces
			$wwwPattern = "/( www )|( www$)|( com )/";
			foreach($newWords as $newWord){
				$tmpQuery = mb_ereg_replace($whiteSpaces, " ", $orignalWords." ".$newWord); // can be speed up by changing the function
				$tmpQuery = mb_ereg_replace("^(\s+)", "", $tmpQuery);
				$newQuerys = $this->QueryReplaceAndCompletion($tmpQuery, $c2);

				if (!empty($newQuerys)){
					//print_r($newQuerys);
					foreach($newQuerys as $newQuery){
						if (preg_match($wwwPattern, $newQuery, $matches)){
							continue;
						}
						$prob = $this->QueryGeneratingProb($c2, $newQuery); // it can replace by google filter				
						//$num = $this->QueryFilter($newQuery);
						$queryPool[$c2][$newQuery] = $prob;
						//$queryPool[$c2][$newQuery] = $num;
					}
				}

			}
			arsort($queryPool[$c2]);
		}
		//return $queryPool;
		
		//rank the completion query
		$completionProb = $this->RankCompletionQueryAcrossConcepts($queryPool);
		//arsort($completionProb);
		return $completionProb;
	}
	public function RankCompletionQueryAcrossConcepts($queryPool){
		// rank Completion Query across different concepts
		$completionProb = array();
		//$pattern = "/( www )|( www com$)|( www$)/";
		foreach ($queryPool as $c2 => $querys){
			foreach ($querys as $q => $prob){
				//if (preg_match($pattern, $q, $matches)){
					//continue;
				//}
				$product = $prob * $this->flowProb[$c2];
				if ( !isset($completionProb[$q]) || $completionProb[$q] < $product){
					$completionProb[$q] = $product;
					// assign new one
					$concept[$q] = $c2;
				}
			}
		}
		arsort($completionProb);
		
		if (count($completionProb) > 20){	
			$qs = array_keys($completionProb);
			for ($i = count($qs) - 1;$i > 0; $i--){ // two different direction
				for ($j = 0;$j <$i; $j++){
					if (levenshtein($qs[$i], $qs[$j]) < 4){
						unset($completionProb[$qs[$i]]);// delete $i
						break;
					}
				}
			}
		}
		/* 
		echo count($completionProb)."\n";
		foreach ($completionProb as $q => $prob){
			echo $q."(concept:". $concept[$q]. ")(prob:".$prob.")\n";
		}*/
		return $completionProb;
	}
	public function GetWordInConcept($clusterNum, $prefix){
		// return a list of words start the input prefix
		$sql = sprintf(
			"select `Word` from `%s`
			where `ClusterNum` = %d and `Word` like '%s%%' 
			order by `NumOfWord` desc", 
			$this->wordTB, $clusterNum, $prefix);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = array();
		while($row = mysql_fetch_row($result)){
			$clusterS[]  = addslashes($row[0] );
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
	public function QueryFilter($querys) {
		// the input is and test querys' array.
		// the output is number of the corresponding result page.
		if (is_array($querys) == false){
			$filter = new QueryGoogle($querys);
			return $num = $filter->NumOfResults();
		}else {
			$nums = array();
			foreach ($querys as $q){
				$filter = new QueryGoogle($q);
				$nums[$q] = $filter->NumOfResults();
			}
			return $nums;
		}		
	}
	public function QueryReplaceAndCompletion($targetQ, $c){
		$querys = $this->GetConceptQuerys($c);
		$newQs = array();
		$newQs[0] = $targetQ;
		if (empty($querys)){
			return $newQs;
		}
		foreach($querys as $q =>$v){
			$complement = $this->GetComplementTerm($targetQ,$q);
			if (!empty($complement)){
				$newQs[] = $targetQ . " " .$complement;
				//echo $complement."\n";
			}			
		}
		return $newQs;
	}
	protected function GetComplementTerm($query, $ref){
		// return the complement terms from $big if $small and $big have some overlapped. 
		// If one word appears in $small, other word appears in $big 
		// and they have the similar LLR sets, they are considered as same meaning.
		// They are also considered as overlapping(can be replaced by each other). 
		// This function will return a string concatenated with the non-overlapping words in $big; 
		//echo $small."\t".$big."\t"."calculating complement:\n";
		$pattern = " ";
		$qTerms = mb_split($pattern, $query);
		$rTerms = mb_split($pattern, $ref);
		//fprintf(STDERR, "query=%s,ref=%s\n", $query,$ref);

		$qLLRSet = $this->GetLLRSet($qTerms);

		$cTerms = array(); // complement terms
		for ($i = 0; $i< count($rTerms); $i++){
			$cTerms[$rTerms[$i]] = 1; // put all terms in rTerms into complement terms as the candidates 
		}		
		$overlap = false;

		// find duplicated terms
		if ( count($qTerms) > 1 ) {
			foreach ($qTerms as $q){
				if ($q == "www" || $q == "com"){
					continue;
				}
				foreach ($rTerms as $r){
					if ($q == $r){
						$overlap = true; // get overlapping
						if (isset($cTerms[$r])){
							unset($cTerms[$r]); // drop the duplicated terms
						}
					}
				}
			}
		}else {// count = 0 or 1
			foreach ($qTerms as $q){
				foreach ($rTerms as $r){
					if ($q == $r){
						$overlap = true; // get overlapping
						if (isset($cTerms[$r])){
							unset($cTerms[$r]); // drop the duplicated terms
						}
					}
				}
			}
		}

		// find duplicated terms with LLRSet
		foreach ($rTerms as $r){
			$rLLR = $this->_GetLLRSet($r);
			foreach ($rLLR as $w => $v){
				if ( isset($qLLRSet[$w]) ){
					//fprintf(STDERR, "get replacement(r=%s,llrW=%s)\n", $r,$w);
					$overlap = true; // get overlapping
					if (isset($cTerms[$r])){
						unset($cTerms[$r]); // drop the duplicated terms
					}
				}	
			}
		}

		if ($overlap == true && !empty($cTerms)){
			$keys = array_keys($cTerms);
			$complement = $keys[0];
			for($i = 1;$i< count($keys) ;$i++){
				$complement .= " ".$keys[$i];
			}
			//echo $query."\t".$ref."\t"."get complement:".$complement."\n";
			return $complement;
		}else{
			// no overlapping, no complement issue;
			//echo "no overlapping or empty cTerms\n";
			return NULL;
		}
	}
	protected function GetLLRSet($words){
		// the return value is an associative array with words as index.
		// the corresponding value is the times of that word appears in different set. 
		if (count($words) == 0){
			fprintf(STDERR,"input words is empty\n");
			return NULL;
		}
		$poolSet = array();
		foreach($words as $w){
			$tmpSet = $this->_GetLLRSet($w);// may be empty
			if (empty($tmpSet)){
				continue;
			}
			foreach ($tmpSet as $i => $v){
				if ( !isset($poolSet[$i]) ){
					$poolSet[$i] = 0;
				}
				$poolSet[$i] += 1;
			}
		}
		//find the most freq words in $poolSet
		$max = 0;
		$LLRSet = array(); // init
		foreach ($poolSet as $w => $v){
			if ($max < $v){
				$LLRSet = array(); // clear;
				$LLRSet[$w] = $v;
				$max = $v;
			}else if ($max = $v){
				$LLRSet[$w] = $v;// add more
			}
		}
		return $LLRSet;
	}
	protected function _GetLLRSet($word){
		//$w = addslashes($word);
		//$w means w2
		if (isset($this->tValue[$word])){
			return $this->tValue[$word];
		}else{
			return array(); // empty array 
		}
	}
	protected function GetConceptQuerys($c){
		// can speed up
		// return a list of Querys in the given concept (cluster)
		$sql = sprintf(
			"select `Query`, `NumOfQuery` from `%s`
			where `ClusterNum` = %d", 
			$this->queryTBTight, $c);

		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = array();
		while($row = mysql_fetch_row($result)){
			$clusterS[ addslashes($row[0]) ] = intval($row[1]);
		}
		return $clusterS;
	}
	private function InitTValue(){
		$sql = sprintf(
			"select `Word1`, `Word2`, `TValue` from `%s`
			where `TValue` > %f", 
			$this->llrTB, $this->llrThreshold);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$TSet = array();
		while($row = mysql_fetch_row($result)){
			//$TSet[w2][w1] = $tvalue
			$TSet[addslashes($row[1])][addslashes($row[0])] = doubleval($row[2]);
		}
		return $TSet;
	}
	private function InitConceptQuerys(){
		$sql = sprintf(
			"select `ClusterNum`, `Query`, `NumOfQuery`  from `%s`", 
			$this->queryTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = array();
		while($row = mysql_fetch_row($result)){
			$clusterS[intval($row[0])][ addslashes($row[1]) ] = intval($row[2]);
		}
		return $clusterS;
	}
	private function InitConceptQuerysCount(){
		$sql = sprintf(
			"select `ClusterNum`, sum(`NumOfQuery`) from `%s` group by `ClusterNum`", 
			$this->queryTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$clusterS = array();
		while($row = mysql_fetch_row($result)){
			$clusterS[intval($row[0])] = intval($row[1]);
		}
		return $clusterS;
	}
	public function GetQueryCombinationWithOtherMethod(){
		// --------------duplaicate partial code from GetQueryCombination -------------
		$words = $this->querySpliter->SplitTerm();
		$conceptPool = $this->GetQueryConceptPool();

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
		// --------------duplaicate partial code end -------------
		$cm = new CompletionMethod($this->queryTB,$this->wordTB, $this->llrTB);
		foreach ($uniqueC2 as $c2 => $value){
			$ret["MostFreq"] = $cm->GetWithMostFreq($c2, $this->q2, 50);
		}
		return $ret;
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

class CompletionMethod{
	protected $queryTB;
	protected $wordTB;
	protected $llrTB;
	public function __construct($qTB, $wTB, $llrTB) {
		$this->queryTB = $qTB;
		$this->wordTB = $wTB;
		$this->llrTB = $llrTB;
	}
	public function GetWithMostFreq($clusterNum, $qPrefix, $minFreq, $limit = -1) {
		$sql = sprintf(
			"select `Query`, `NumOfQuery` from `%s`
			where `ClusterNum` = %d and `Query` like '%%%s%%' 
			and `NumOfQuery` >= %d
			group by `Query`
			order by `NumOfQuery` desc
			", 
			$this->queryTB, $clusterNum, $qPrefix, $minFreq);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());

		echo $sql."\n";
		$clusterS = array(); 
		if ($limit == -1 || $limit > mysql_num_rows($result)){
			$limit = mysql_num_rows($result);
		}
		$i = 0;
		while($i < $limit){
			$row = mysql_fetch_row($result);
			$clusterS[] = addslashes($row[0]);
			$i++;
		}
		return $clusterS;
	}
}
?>
