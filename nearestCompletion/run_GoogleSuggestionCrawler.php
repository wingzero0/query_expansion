<?php
// sample usage:
// php run_GoogleSuggestionCrawler.php -ub 160000 -lb 60000

require(dirname(__FILE__)."/GoogleSuggestionCrawler.php");

$para = ParameterParser($argc, $argv);
$keys = array("ub", "lb");
$ret = ParameterChecking($keys, $para);

$obj = new GoogleSuggestionCrawler($para["ub"], $para["lb"], "Aol_SingleQ");
$obj->LoadDb();

?>