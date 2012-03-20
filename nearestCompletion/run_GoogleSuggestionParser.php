<?php
// sample usage:
// php run_GoogleSuggestionCrawler.php -path googleHtml/

require(dirname(__FILE__)."/GoogleSuggestionCrawler.php");

$para = ParameterParser($argc, $argv);

$keys = array("path");
$ret = ParameterChecking($keys, $para);

$obj = new GoogleSuggestionCrawler(0, 0, "Aol_SingleQ"); // the argument is useless
$obj->PathFileRecommendationToDb($para["path"]);

?>