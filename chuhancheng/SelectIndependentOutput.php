<?php
// The program will select the independent Aol query pairs.
// if the pair not pass t-test, it is considered as independent pairs


class SelectIndependentOutput{
	public function SelectIndependent($allData, $dependentData){
		//$independent = array();
		//$independent["order"] = array();
		//$independent["content"] = array();
		
		$order = array(); // independentOrder
		$content = array();// independentContent
		foreach ($allData["order"] as $i => $qPairs){
			$list = split("\t", $qPairs);
			$q1 = $list[0];
			$q2 = $list[1];
			if ( !isset($dependentData["content"][$q1][$q2]) ){ // get independent data
				$order[] = $qPairs;
				$content[$q1][$q2] = $allData["content"][$q1][$q2];
			}
		}
		$ret["order"] = $order;
		$ret["content"] = $content;
		return $ret;
	}
	public function OutputIndependent($data, $filename){
		$fp = fopen($filename, "w");
		if ($fp == NULL){
			fprintf(STDERR, "%s opened error\n", $filename);
			return -1;
		}
		foreach ($data["order"] as $i => $qPairs){
			$list = split("\t", $qPairs);
			$q1 = $list[0];
			$q2 = $list[1];
			fprintf($fp, "%d\t%s\t%s\n", $data["content"][$q1][$q2]["value"],$q1, $q2);
			if ( isset($data["content"][$q1][$q2]["results"]) ){
				foreach ($data["content"][$q1][$q2]["results"] as $q => $rank){
					fprintf($fp, "\t%s\n", $q);
				}
			}
		}
		fclose($fp);
		return 0;
	}
	public function LoadSourceFile($filename){
		$fp = fopen($filename, "r");
		if ($fp == NULL){
			fprintf(STDERR, "%s opened error\n", $filename);
			return array();
		} 
		$order = array();
		while($line = fgets($fp)){
			$line = trim($line);
			$list = split("\t", $line);
			if ( count($list) != 3){
				fprintf(STDERR, "%s format error\n", $filename);
				fprintf(STDERR, "%s\n", $line);			
				break;
			}
			$q1 = $list[1];
			$q2 = $list[2]; // q2 is ground truth
			$order[] = $q1 . "\t" . $q2; // sync the order, let people can compare with original result
			$content[$q1][$q2]["value"] = $list[0];
		}
		fclose($fp);
		$ret["order"] = $order;
		$ret["content"] = $content;
		return $ret;
	}
	public function LoadResultFile($filename){
		// copy from SyncOutputOrder.php
		$fp = fopen($filename, "r");
		if ($fp == NULL){
			fprintf(STDERR, "%s opened error\n", $filename);
			return array();
		} 
		$line = fgets($fp);
		$flag = false;// flag is a signal about found the next query.
		$content = array();
		$order = array(); // numeric array
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
			$order[] = $q1 . "\t" . $q2; // sync the order, let people can compare with original result
			$content[$q1][$q2]["results"] = array();
			$content[$q1][$q2]["value"] = $list[0];
			$i = 1;
			while($line = fgets($fp)){
				$line = trim($line);
				$list = split("\t", $line);
				//print_r($list);
				if ( count($list) == 3 ){
					$flag = true;
					break;
				}else if ( count($list) == 1 ){					
					$content[$q1][$q2]["results"][$list[0]] = $i;
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
		$ret["order"] = $order;
		$ret["content"] = $content;
		fclose($fp);
		return $ret;
	}
}
?>
