<?php
// class RecommendationTree construct the data set of the NearestCompletion method 
define("DB",1);
define("FILE",2);
require_once(dirname(__FILE__)."/../connection.php");

mysql_select_db($database_cnn,$b95119_cnn);

class RecommendationTree{
	private $rTb; //related Query table name
	private $ngramTb; // n-gram list table
	private $vectorTb; // n-gram table with vector 
	private $trees;
	public function __construct(){
		$this->trees = null;
	}
	public function LoadRelatedQFile($filename){
		$fp = fopen($filename, "r");
		if ($fp == null){
			fprintf(STDERR,"%s can't be open\n", $filename);
			return array();
		}
		$relatedQ = array();
		while ( $line = fgets($fp) ){
			$list = preg_split("/\t|\n/", trim($line) );
			if (count($list) != 2){
				fprintf(STDERR,"line error:\n%s", $line);
				continue;
			}
			$q1 = addslashes($list[0]);
			$q2 = addslashes($list[1]);
			if ( !isset($relatedQ[$q1]) ){
				$relateQ[$q1] = array();
			}
			$relatedQ[$q1][] = $q2;
		}
		fclose($fp);
		return $relatedQ;
	}
	public function LoadRelatedQDB($relateTb){
		$sql = sprintf(
			"select `q1`, `q2` from `%s` where `q1` != `q2`", $relateTb
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
		/*
		foreach ($relatedQ as $q1 => $v){
			$trees[$q1][1] = array();
			foreach ($v as $q2){
				$trees[$q1][1][] = $q2;
			}
		}*/
		foreach ($relatedQ as $q1 => $v){
			$trees[$q1][0][0] = $q1;
		}
		foreach ($trees as $q1 => $layer){
			$unique = array(); // reduce loop expand
			$unique[$q1] = true;
			for ($i = 0; $i < $depth; $i++){
				if ( !isset($trees[$q1][$i]) ){
					break;
				}
				//print_r($trees[$q1][$i]);
				foreach ($trees[$q1][$i] as $j => $q2){
					if ( isset($relatedQ[$q2]) ){
						foreach ($relatedQ[$q2] as $k => $q3){
							if ( !isset($unique[$q3]) ){
								$trees[$q1][$i+1][] = $q3;
								$unique[$q3] = true;
							}
						}
					}
				}
			}
		}
		$this->trees = $trees;
		return $trees;
	}
	public function SaveTreeNgramDB($trees, $ngramTb){		
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
					$ngramTb , $q1, $ngram
				);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
	public function NgramIDF($flag, $sourceName) {
		$idf = array();
		if ($flag == DB){
			echo "reading DB\n";
			
			$sql = sprintf(
				"SELECT count(distinct(`query`)) FROM `%s`", $sourceName
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
				$rq = $this->LoadRelatedQFile($sourceName);
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
				// counting
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
	public function SaveVectorDB($vector, $vectorTb){
		foreach($vector as $q1 => $ngrams){
			foreach ($ngrams as $ngram => $v){
				$sql = sprintf(
					"insert into `%s` (`query`, `ngram`, `value`) values ('%s', '%s', %lf)",
					$vectorTb , $q1, $ngram, $v
					);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
	public function SaveVectorFile($vector, $filename){
		$fp = fopen($filename, "w");
		if ($fp == null){
			fprintf(STDERR,"%s can't be open\n", $filename);
			return;
		}
		foreach($vector as $q1 => $ngrams){
			foreach ($ngrams as $ngram => $v){
				fprintf($fp , "%s\t%s\t%lf\n", $q1, $ngram, $v);
			}
		}
		fclose($fp);
	}
	public function SaveVectorFromFileToDB($filename, $vectorTb){
		$fp = fopen($filename, "r");
		if ($fp == null){
			fprintf(STDERR,"%s can't be open\n", $filename);
			return;
		}
		while ( $line = fgets($fp) ){
			$list = preg_split("/\t/", trim($line) );
			if (count($list) != 3){
				fprintf(STDERR,"line error:\n%s", $line);
				continue;
			}
			$q1 = $list[0];
			$ngram = $list[1];
			$v = doubleval($list[2]);
			$sql = sprintf(
				"insert into `%s` (`query`, `ngram`, `value`) values ('%s', '%s', %lf)",
				$vectorTb , $q1, $ngram, $v
			);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
		fclose($fp);
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
