<?php
// sample usage:
// php run_GoogleSuggestionCrawler.php -ub 160000 -lb 60000 -path googleHtml/

require(dirname(__FILE__)."/GoogleSuggestionCrawler.php");

$para = ParameterParser($argc, $argv);

$keys = array("ub", "lb", "path");
$ret = ParameterChecking($keys, $para);

$obj = new GoogleSuggestionCrawler($para["ub"], $para["lb"], "Aol_SingleQ");
$obj->SaveHtmlOnly($para["path"]);
//$obj->LoadDb();

?>