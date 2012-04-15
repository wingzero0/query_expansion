<?php
// Class QueryDiversity will rerank the query with 3 similarity
// div = - maxSim(q*, q^), 
// 1. if q* and q^ are in some concept sim = 1, else sim = 0,
// 2. if q* and q^ are in some concept sim = 1, else sim = sim(c_q*, c_q^),
// 3. sim = ngramVector(q*, q^) 

require_once(dirname(__FILE__)."/connection.php");

mysql_select_db($database_cnn,$b95119_cnn);

class QueryDiversity{
	public $qList;
	public $vectorTb;
	public $relevantRank;
	public function __construct($vectorTb){
		$this->vectorTb = $vectorTb;
	}
	public function LoadFile($filename){
		// copy from SelectDifferent.php
		$fp = fopen($filename, "r");
		if ($fp == NULL){
			fprintf(STDERR, "%s opened error\n", $filename);
			return array();
		} 
		$line = fgets($fp);
		$flag = false;// flag is a signal about found the next query.
		$content = array();
		while(1){
			$line = trim($line);
			$list = split("\t", $line);
			//print_r($list);
			if ( count($list) != 3){
				//echo "EOF?\n";
				break;
			}
			$q1 = $list[1];
			$q2 = $list[2]; // q2 is ground truth
			$content[$q1][$q2]["results"] = array();
			$content[$q1][$q2]["value"] = $list[0];
			$i = 1;
			while($line = fgets($fp)){
				//$line = trim($line);
				$list = split("\t", $line);
				//print_r($list);
				if ( count($list) == 3 ){
					$flag = true;
					break;
				}else if ( count($list) == 4 ){					
					$content[$q1][$q2]["results"][$list[1]] = $i;
					$content[$q1][$q2]["concept"][$list[1]] = intval($list[2]);
					$content[$q1][$q2]["score"][$list[1]] = doubleval($list[3]);
					$i++;
				}
			}
			if ($flag == true){
				$flag = false;
				continue;
			}
			if ($line = fgets($fp)){ // end of file
				break;
			}
		}
		fclose($fp);
		return $content;
	}
	
	public function DivRank($content, $outputFile) {
		$fp = fopen($outputFile, "w");
		if ($fp == NULL){
			fprintf(STDERR, "%s opened error\n", $outputFile);
			return -1;
		}
		foreach ($content as $q1 => $array1){
			foreach ($array1 as $q2 => $attribute){
				if ( !empty($attribute["results"]) ){
					$relevantRank = $attribute["score"]; 
					$mergeRank = $this->DivRankWithNgramTree($relevantRank);
				}else{
					$mergeRank = array();
				}
				$this->WriteFile($fp, $q1, $q2, $attribute["value"],$mergeRank);
			}
		}
		fclose($fp);
	}
	public function Normalize($Rank){
		$nRank = array();
		if ( !empty($Rank) ){
			foreach($Rank as $q => $v){
				$max = $v;// get the first one and it's the max value in rank list
				break;
			}
			foreach($Rank as $q => $v){
				$nRank[$q] = $v / $max;
			}
		}
		return $nRank;
	}
	public function DivRankWithNgramTree($relevantRank){
		$candidateQ = array(); //associative array 
		$selectedQ = array(); //normal array
		$qVector = array(); //associative array
		$qLength = array(); //associative array
		//print_r($relevantRank);
		$relevantRank = $this->Normalize($relevantRank);
		foreach ($relevantRank as $q => $v){
			$qVector[$q] = $this->GetNgramVector($q);
			$qLength[$q] = $this->VectorLength($qVector[$q]);
			$candidateQ[$q] = true;
		}
		while( !empty($candidateQ) ){
			$candidateQSim = $this->VectorReverseSim($qVector, $qLength,$selectedQ, $candidateQ); // $update canddidateQSim
			$ret = $this->SelectMMR($relevantRank, $candidateQSim, 0.5);
			unset($candidateQ[$ret["q"]]);
			$selectedQ[] = $ret["q"];
			$score[$ret["q"]] = $ret["score"];
		}
		//print_r($score);
		//$selectedQ wan't have a score.
		return $selectedQ; 
	}
	public function SelectMMR($relevantRank, $candidateQSim, $alpha){
		// select q = argmax($relevantRank[$q] * a + $candidiateQSim[$q] * (1-a));
		$ret["score"] = 0.0;
		$ret["q"] = NULL; 
		foreach ($candidateQSim as $q => $sim){
			$tmp = $relevantRank[$q] * $alpha + $sim * (1.0 - $alpha);
			if ($tmp > $ret["score"]){
				$ret["score"] = $tmp;
				$ret["q"] = $q;
			}
		}
		return $ret;
	}
	public function VectorLength($vector){
		$tmpLength = 0.0;
		foreach ($vector as $ngram => $value){
			$tmpLength += $value * $value;
		}
		$qLength = sqrt($tmpLength);
		return $qLength;
	}
	public function VectorReverseSim($qVector, $qLength, $selectedQ, $candidateQ) {
		// identify minimumm similarity of each query in candidate set to the selected set
		// and reverse the score with 1 - x
		$candidateQSim = array();
		foreach ($candidateQ as $q => $value){
			$sim = 1.0; // max sim
			foreach ($selectedQ as $qs){
				$tmpSim = $this->Similarity($qVector[$q], $qVector[$qs], $qLength[$q], $qLength[$qs]);
				//exit(0);
				if ($sim > $tmpSim){
					$sim = $tmpSim;
				}
			}
			$candidateQSim[$q] = 1.0 - $sim;
		}
		return $candidateQSim;
	}
	protected function GetNgramVector($q){
		$sql = sprintf("select `ngram`,`value` from `%s` where `query` = '%s'", $this->vectorTb, addslashes($q));
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$vector =array();
		while($row = mysql_fetch_row($result)){
			$vector[$row[0]] = doubleval($row[1]); // no slash?
		}
		if ( empty($vector) ){
			
		}
		return $vector;
	}
	protected function Similarity($v1, $v2, $v1Length = -1, $v2Length = -1){
		if ($v1Length == -1){
			$tmpLength = 0.0;
			foreach ($v1 as $ngram => $value){
				$tmpLength += $value * $value;
			}
			$v1Length = sqrt($tmpLength);
		} // eles use the input value
		
		if ($v2Length == -1){
			$tmpLength = 0.0;
			foreach ($v2 as $ngram => $value){
				$tmpLength += $value * $value;
			}
			$v2Length = sqrt($tmpLength);
		}
		
		if ($v1Length == 0.0 || $v2Length == 0.0){
			return 0.0;
		}
		$sum = 0.0;
		foreach($v1 as $ngram => $value){
			if ( isset($v2[$ngram]) ){
				$sum += $value * $v2[$ngram];
			}
		}
		$sum = $sum / ($v1Length * $v2Length);
		return $sum;
	}
	public function WriteFile($fp, $q1, $q2, $value, $mergeRank){
		fprintf($fp, $value."\t".$q1."\t".$q2."\n");
		$i = 0;
		foreach ($mergeRank as $q){
			if ($i >=10){
				break;
			}
			fprintf($fp, "\t".$q."\n");
			$i++;
		}
		
	}
	public static function test(){
		$filename = "chuhancheng/out.txt";
		$obj = new QueryDiversity("NgramVector");
		$content = $obj->LoadFile($filename);
		//print_r($content);
		$obj->DivRank($content, "chuhancheng/out2.txt");
	}
}

//QueryDiversity::test();

?>