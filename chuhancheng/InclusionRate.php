<?php
//require_once(dirname(__FILE__)."/connection.php");
//mysql_select_db($database_cnn,$b95119_cnn);

class InclusionRate{
	public $maxTop;
	public $hitAt;
	public $N;
	public function __construct($maxTop) {
		$this->maxTop = $maxTop;
		$this->N = 0;
	}
	protected function AddRecord($gt, $results){
		for ($i = 0;$i< count($results); $i++){
			if ($gt == $results[$i]){
				if ( !isset($this->hitAt[$i + 1]) ){
					$this->hitAt[$i + 1] = 0;
				}
				$this->hitAt[$i + 1] +=1;
			}
		}
		$this->N +=1;
	}
	public function InclusionRateAtN($n){
		ksort($this->hitAt);
		$sum = 0;
		foreach($this->hitAt as $i=>$v){
			if ($i > $n){
				break;
			}
			$sum += $v;
		}
		$rate = (double) $sum / (double) $this->N;
		return $rate;
	}
	public function InclusionRateUntilN($n){
		ksort($this->hitAt);
		$sum = 0;
		$rates = array();
		for ($i = 1; $i<= $n;$i++){
			if ( isset($this->hitAt[$i]) ){
				$sum += $this->hitAt[$i];
			}
			$rates[$i] = (double) $sum / (double) $this->N;
		}
		return $rates;
	}
	public function SimpleReadFile($filename){
		$fp = fopen($filename, "r");
		$line = fgets($fp);
		$flag = false;
		while(1){
			$line = trim($line);
			$list = split("\t", $line);
			//print_r($list);
			if ( count($list) != 3){
				//echo "EOF?\n";
				break;
			}
			$gt = $list[2];
			$results = array(); // clean
			while($line = fgets($fp)){
				$line = trim($line);
				$list = split("\t", $line);
				//print_r($list);
				if ( count($list) > 1 ){
					$this->AddRecord($gt, $results);
					$flag = true;
					break;
				}else{
					$results[] = $list[0];
					//echo "stack result\n";
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
