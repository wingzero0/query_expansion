<?php
//require_once(dirname(__FILE__)."/connection.php");
//mysql_select_db($database_cnn,$b95119_cnn);

class EvaluationBase{
	public function __construct(){}
	public function GetHitAt($gt, $results){
		// return -1 means no hit
		$wwwPattern = "/(^www )|( com$)/";
		$gt_m = preg_replace($wwwPattern, "", $gt);
		for ($i = 0;$i< count($results); $i++){
			if ( $gt == $results[$i] || $gt_m == $results[$i] ){
				return $i+1;
			}
		}
		return -1;
	}
	public function AddRecord(){
		// abstract method
		printf("this is an abstract method, subclass must implement this function");
		exit();
	}
	public function SimpleReadFile($filename){
		// read the file, and calculate each record's score with $this->AddRecord(); 
		$this->filename = $filename;
		$fp = fopen($filename, "r");
		$line = fgets($fp);
		$flag = false;
		while(1){
			// get the test pair. first q is context, second q is ground truth
			$line = trim($line);
			$list = split("\t", $line);
			if ( count($list) != 3){
				//echo "EOF?\n";
				break;
			}
			$gt = $list[2];
			$results = array(); // clean
			while($line = fgets($fp)){
				// get recommendation until next test pair.
				$line = trim($line);
				$list = split("\t", $line);
				if ( count($list) > 1 ){ 
					// meet next test pair. calculate the score of current record   
					$this->AddRecord($gt, $results);
					$flag = true;
					break;
				}else{
					// store records
					$results[] = $list[0];
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
	}
}
?>
