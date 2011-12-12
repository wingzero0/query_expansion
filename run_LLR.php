<?php
//sample usage:
//php run_LLR.php -concurrent matrix_test -single word_test -llr llr_test -N 30840188 
require("/home/b95119/query_expansion/LLR2.php");

$para = ParameterParser($argc, $argv);
$keys = array("concurrent", "single", "llr", "N");
$ret = ParameterChecking($keys, $para);
$obj = new LLR($para["concurrent"], $para["single"], $para["llr"], 
	intval($para["N"]));
$ret = $obj->Run();
?>
