<?php
// class RecommendationTree construct the data set of the NearestCompletion method 
define("DB",1);
require_once(dirname(__FILE__)."/../connection.php");

mysql_select_db($database_cnn,$b95119_cnn);

class RecommendationTree{
	private $rTb; //related Query table name
	private $ngramTb; // n-gram list table
	private $vectorTb; // n-gram table with vector 
	private $trees;
	public function __construct($relateTb, $tmpNgramTb, $vectorTb){
		$this->rTb = $relateTb;
		$this->ngramTb = $tmpNgramTb;
		$this->vectorTb = $vectorTb;
		$this->trees = null;
	}
	public function LoadRelatedQ(){
		$sql = sprintf(
			"select `q1`, `q2` from `%s`", $this->rTb
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$relatedQ = array();
		while($row = mysql_fetch_row($result)){
			//echo $row[0]."\n";
			$q1 = addslashes($row[0]);
			$q2 = addslashes($row[1]);
			if ( !isset($relatedQ[$q1]) ){
				$relateQ[$q1] = array();
			}
			$relatedQ[$q1][] = $q2;
		}
		return $relatedQ;
	}
	public function ConstructTrees($relatedQ, $depth) {
		$trees = array();
		foreach ($relatedQ as $q1 => $v){
			$trees[$q1][0] = array();
			foreach ($v as $q2){
				$trees[$q1][0][] = $q2;
			}
		}
		
		foreach ($trees as $q1 => $layer){
			for ($i = 0; $i < $depth; $i++){
				if ( !isset($trees[$q1][$i]) ){
					break;
				}
				//print_r($trees[$q1][$i]);
				foreach ($trees[$q1][$i] as $j => $q2){
					if ( isset($relatedQ[$q2]) ){
						foreach ($relatedQ[$q2] as $k => $q3){
							$trees[$q1][$i+1][] = $q3;
						}
					}
				}
			}
		}
		$this->trees = $trees;
		return $trees;
	}
	public function SaveTreeNgram($trees){		
		foreach ($trees as $q1 => $depthArray){
			$unique = array(); // clear
			foreach( $depthArray as $i => $qArray ){
				foreach ($qArray as $q2){
					$ngrams = preg_split("/\s/", $q2);
					// it is unigram
					foreach ($ngrams as $ngram){
						if (!empty($ngram)){
							$unique[$ngram] = true;
						}
					}
				}
			}
			foreach($unique as $ngram => $v){
				$sql = sprintf(
					"insert into `%s` (`query`, `ngram`) values ('%s', '%s')",
					$this->ngramTb , $q1, $ngram
				);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
	public function NgramIDF($flag) {
		$idf = array();
		if ($flag == DB){
			echo "reading DB\n";
			
			$sql = sprintf(
				"SELECT count(distinct(`query`)) FROM `%s`", $this->ngramTb
			);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
			$row = mysql_fetch_row($result);
			$numQ = doubleval( $row[0] );
			
			$sql = sprintf(
				"select `ngram`, count(`ngram`) 
				from `%s`
				group by `ngram`
				", $this->ngramTb
			);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
			while($row = mysql_fetch_row($result)){
				//echo $row[0]."\n";
				$ngram = addslashes($row[0]);
				$num = doubleval($row[1]);
				$idf[$ngram] = $numQ / $num;
			}
		}else {
			if ($this->trees == null){
				$rq = $this->LoadRelatedQ();
				$this->trees = $this->ConstructTrees($rq, 2);
			}
			$unique = array();
			$numQ = 0;
			foreach ($this->trees as $q1 => $depthArray){
				$numQ +=1;
				$tmp =array(); // clear
				foreach( $depthArray as $i => $qArray ){
					foreach ($qArray as $q2){
						$ngrams = preg_split("/\s/", $q2);
						// it is unigram
						foreach ($ngrams as $ngram){ // remove the duplicate term from the same query
							if (!empty($ngram)){
								$tmp[$ngram] = true;
							}
						}
					}
				}
				foreach ($tmp as $ngram => $v){
					if ( !isset($unique[$ngram]) ){
						$unique[$ngram] = 0;
					}
					$unique[$ngram]++;
				}
			}
			//print_r($unique);
			$idf = array();
			foreach ($unique as $ngram => $v){
				$idf[$ngram] = (double) $numQ / (double)($v);
				//echo $ngram." ".$idf[$ngram]."\n";
			}
		}
		return $idf;
	}
	public function ConstructVector($idf, $trees){
		foreach ($trees as $q1 => $depthArray){
			$vector[$q1] = array(); // clear
			foreach( $depthArray as $i => $qArray ){
				$depthW = $this->DepthWeight($i);
				foreach ($qArray as $q2){
					$ngrams = preg_split("/\s/", $q2);
					// it is unigram
					foreach ($ngrams as $ngram){
						if ( !empty($ngram) ){
							if ( !isset($vector[$q1][$ngram]) ){
								$vector[$q1][$ngram] = 0.0; //init
							}
							if ( isset( $idf[$ngram] )){
								$vector[$q1][$ngram] += $depthW * log($idf[$ngram]);
							}
						}
					}
				}
			}
		}
		return $vector;
	}
	public function SaveVector($vector){
		foreach($vector as $q1 => $ngrams){
			foreach ($ngrams as $ngram => $v){
				$sql = sprintf(
					"insert into `%s` (`query`, `ngram`, `value`) values ('%s', '%s', %lf)",
					$this->vectorTb , $q1, $ngram, $v
					);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
	public static function DepthWeight($depth){
		if ($depth >=0){
			return 1.0 / exp ($depth);
		}else {
			return 0.0; // error
		}
	}
	public static function test(){
		$obj = new RecommendationTree("RelatedQuery","AolNgramTree", "AolNgramVector");
		$rq = $obj->LoadRelatedQ();
		$trees = $obj->ConstructTrees($rq, 2);
		//echo "trees:\n";
		//print_r($trees);
		//$obj->SaveTreeNgram($trees);
		$idf = $obj->NgramIDF(DB);
		//echo "idf:\n";
		//print_r($idf);
		$vector = $obj->ConstructVector($idf, $trees);
		//echo "vector:\n";
		//print_r($vector);
		$obj->SaveVector($vector);
	}
}

//RecommendationTree::test();

?>
