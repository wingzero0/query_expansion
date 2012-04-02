<?php
// do the pre-process of CoClickQuerySuggestion
// sample usage:
// php CoClickPreProcess.php -logFile clean_click_5.txt -saveTb q2q  

require_once(dirname(__FILE__)."/CoClickQuerySuggestion.php");

$para = ParameterParser($argc, $argv);

$keys = array("logFile", "saveTb");
$ret = ParameterChecking($keys, $para);

$obj = new CoClickQuerySuggestion();
$obj->InitFromFile($para["logFile"]);
$obj->SaveQ2QDB($para["saveTb"]);

?>