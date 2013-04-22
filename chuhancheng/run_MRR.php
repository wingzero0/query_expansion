<?php
require_once "/home/b95119/mylib/kit_lib.php";
require_once dirname(__FILE__)."/EvaluationClasses/MRR.php";

$para = ParameterParser($argc, $argv);
$obj = new MRR();
$obj->SimpleReadFile($para["input"]);
//$obj->SimpleReadFile(dirname(__FILE__). "/baseline_all.txt");^M
$score = $obj->GetEverageScore();
echo $score;

?>
