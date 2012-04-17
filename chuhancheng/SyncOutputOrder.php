<?php
// The program synchronize the disorder output.
// let all output have the same order, easy for human to analyst the result
// sample usage:
// php SyncOutputOrder.php targetOrderFilename filename2


class SyncOutputOrder{
	//public function __construct() {
	//}
	protected static function LoadFile($filename){
		// copy from SelectDifferent.php
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
	public static function SyncOutput($targetOrderFilename, $otherFilename){
		$targetResult = SyncOutputOrder::LoadFile($targetOrderFilename);
		$otherResult = SyncOutputOrder::LoadFile($otherFilename);
		$newFile = $otherFilename.".fixed";
		SyncOutputOrder::WriteFile($targetResult["order"], $otherResult["content"], $newFile);
	}
	public static function WriteFile($order, $content, $outputFile){
		$fp = fopen($outputFile, "w");
		if ($fp == NULL){
			fprintf(STDERR, "%s opened error\n", $outputFile);
			return -1;
		}
		for ($i = 0;$i< count($order);$i++){
			$list = preg_split("/\t/", $order[$i]);
			//print_r($list);
			//continue;
			$q1 = $list[0];
			$q2 = $list[1];
			if (  !isset($content[$q1][$q2]) ){
				fprintf(STDERR, $attribute["value"]."\t".$q1."\t".$q2."\n");
			}else{
				$attribute = $content[$q1][$q2];
				fprintf($fp, $attribute["value"]."\t".$q1."\t".$q2."\n");
				if ( !empty($attribute["results"]) ){
					foreach ($attribute["results"] as $q => $rank ){
						fprintf($fp, "\t".$q."\n");
					}
				}
			}
		}
		fclose($fp);
	}
}

//SyncOutputOrder::SyncOutput("version_5_all/ttest_0_2/completion_2.5up.txt", 
//	"version_5_all/ttest_0_2/completionDiversity_2.5up.txt");

SyncOutputOrder::SyncOutput($argv[1], $argv[2]);

?>
