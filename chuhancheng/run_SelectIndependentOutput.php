<?php
//sample usage:
//php run_SelectIndependentOutput.php -all allResultFile.txt -d dependentSource.txt -o independent.txt 
require("/home/b95119/query_expansion/chuhancheng/SelectIndependentOutput.php");
require("/home/b95119/mylib/kit_lib.php");

$para = ParameterParser($argc, $argv);
$obj = new SelectIndependentOutput();
$dependent = $obj->LoadSourceFile($para["d"]);
$all = $obj->LoadResultFile($para["all"]);
$independent = $obj->SelectIndependent($all, $dependent);
$obj->OutputIndependent($independent, $para["o"]);

?>
