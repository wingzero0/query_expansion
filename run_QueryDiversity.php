<?php
//sample usage:
// php run_QueryDiversity.php -vTb NgramVector -o out.txt -i in.txt 

require(dirname(__FILE__)."/QueryDiversity.php");

$para = ParameterParser($argc, $argv);
$keys = array("vTb", "o", "i");
$ret = ParameterChecking($keys, $para);
$obj = new QueryDiversity($para["vTb"]);
$ret = $obj->LoadFile($para["i"]);
$obj->DivRank($ret["content"], $ret["order"], $para["o"], VECTOR);
?>
