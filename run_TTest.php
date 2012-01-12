<?php
//sample usage:
//php run_TTest.php -concurrent matrix_test -single word_test -ttest t_test -N 25623994
require("/home/b95119/query_expansion/TTest.php");

$para = ParameterParser($argc, $argv);
$keys = array("concurrent", "single", "t", "N");
$ret = ParameterChecking($keys, $para);
$obj = new TTest($para["concurrent"], $para["single"], $para["t"], intval($para["N"]));
$ret = $obj->Run();
?>
