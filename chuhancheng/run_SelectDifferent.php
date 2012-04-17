<?php
//sample usage:
//php run_SelectDifferent.php -f1 baseline.txt -f2 completion.txt -o1 BaselineOnly -o2 CompletionOnly
require("/home/b95119/query_expansion/chuhancheng/SelectDifferent.php");
require("/home/b95119/mylib/kit_lib.php");

$para = ParameterParser($argc, $argv);
$obj = new SelectDifferent($para["f1"], $para["f2"], $para["o1"], $para["o2"]);
$obj->FindDifferent();
?>
