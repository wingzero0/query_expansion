<?php

require_once dirname(__FILE__)."/EvaluationBaseClass.php";

class MRR extends EvaluationBase{
	protected $num; // num of record
	protected $totalScore;
	public function __construct(){
		parent::__construct();
		$this->num = 0;
		$this->totalScore = 0.0;
	}
	public function GetScore($gt, $results, $weight = 1.0){
		$pos = $this->GetHitAt($gt, $results);
		if ($pos == -1){
			return 0;
		}else {
			return 1.0 / (doubleval($pos)) * $weight;
		}
	}
	public function GetEverageScore(){
		return $this->totalScore / doubleval($this->num);
	}
	public function GetTotalScore(){
		return $this->totalScore;
	}
	public function GetNum(){
		return $this->num();
	}
	public function AddRecord($gt, $results){
		$weight = count($results);
		$score = $this->GetScore($gt, $results, $weight);
		$this->num +=1;
		$this->totalScore += $score;
		return $score;
	}
}

require_once "/home/wingzero/mylib/kit_lib.php";

//$para = ParameterParser($argc, $argv);
$obj = new MRR();
//$obj->SimpleReadFile($para["input"]);
$obj->SimpleReadFile(dirname(__FILE__). "/baseline_all.txt");
$score = $obj->GetEverageScore();
echo $score."<br>\n";

$obj = new MRR();
//$obj->SimpleReadFile($para["input"]);
$obj->SimpleReadFile(dirname(__FILE__). "/nearest_all.txt");
$score = $obj->GetEverageScore();
echo $score."<br>\n";

$obj = new MRR();
//$obj->SimpleReadFile($para["input"]);
$obj->SimpleReadFile(dirname(__FILE__). "/completion_all.txt");
$score = $obj->GetEverageScore();
echo $score."<br>\n";
?>