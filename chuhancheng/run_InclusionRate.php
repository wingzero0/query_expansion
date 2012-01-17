<?php
//sample usage:
//php run_InclusionRate.php -input completion.txt

require("/home/b95119/query_expansion/chuhancheng/InclusionRate.php");
require("/home/b95119/mylib/kit_lib.php");

$para = ParameterParser($argc, $argv);
$obj = new InclusionRate(10);
$obj->SimpleReadFile($para["input"]);
$rates = $obj->InclusionRateUntilN(10);
//print_r($rates);
foreach ($rates as $i => $v){
	echo $i."\t".$v."\n";
}
?>
