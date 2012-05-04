<?php
// random selete 200 record in the input file 
// each line contains one record.

class RandomSample{
	public function __construct(){
		srand(1);
	}
	public function RandomSwap($data, $size, $randomSwapCount = 10){
		for ($i = 0;$i < $randomSwapCount; $i++){
			$index1 = rand(0, $size -1);
			$index2 = rand(0, $size -1);
			$tmp = $data[$index1];
			$data[$index1] = $data[$index2];
			$data[$index2] = $tmp; 
		}
		return $data;
	}
	public function GetNRandomResults($dataSize, $n){
		for ($i = 0;$i < $dataSize;$i++){
			$data[$i] = $i;
		}
		$data = $this->RandomSwap($data, $dataSize, $dataSize);
		for ($i = 0;$i < $n;$i++){
			$ret[$i] = $data[$i];
		} 
		return $ret;
	}
	public function ReadStatisticsData($filename){
		$fp = fopen($filename, "r");
		if ($fp == null){
			echo $filename." can't be open<br>";
			return array();
		}
		$data = array();
		while ($line = fgets($fp)){
			$data[] = $line;
		}
		
		fclose($fp);
		return $data;
	}
	public function SelectTargetData($data, $index){
		//sort($index);
		foreach($index as $i=>$v){
			$ret[$i] = $data[$v];
		}
		return $ret;
	}
	/*
	public function RandomSelect(){
		for ($i = 0;$i < 100;$i++){
			$x = rand(0, 3);
			$y = rand(1, 5);
			//$select[$i] = $x . "_" .$y;
			if ( !isset($select[$x."_".$y]) ){
				$select[$x . "_" .$y] = 0;
			} 
			$select[$x . "_" .$y] += 1;
		}
		ksort($select);
		return $select;
	}
	 */
	public static function test(){
		$obj = new RandomSample();
		//$ret = $obj->RandomSelect();
		//for ($i = 0;$i < 100;$i++){
		//	$data[$i] = $i;
		//}
		$record = $obj->ReadStatisticsData("chuhancheng/Aol_pair_nqq2/Aol_pair_nqq_all.txt");
		$index = $obj->GetNRandomResults(10601, 200);
		$data = $obj->SelectTargetData($record, $index);
		print_r($index);
		print_r($data);
		$fp = fopen("userStudy.txt", "w");
		foreach($data as $i => $v){
			fprintf($fp, "%s", $v);
		}
		fclose($fp);
		//print_r($record);
	}
}

RandomSample::test();
?>
