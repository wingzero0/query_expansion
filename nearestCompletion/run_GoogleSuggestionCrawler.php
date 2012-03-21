<?php
// sample usage:
// php run_GoogleSuggestionCrawler.php -qFile ./Msn_SingleQ.txt -savePath MsnQueryHtml/
// php run_GoogleSuggestionCrawler.php -ub 160000 -lb 60000 -path googleHtml/

require(dirname(__FILE__)."/GoogleSuggestionCrawler.php");

$para = ParameterParser($argc, $argv);

$keys = array("qFile", "savePath");
$ret = ParameterChecking($keys, $para);

$obj = new GoogleSuggestionCrawler();
$obj->QueryFileToHtml($para["qFile"],$para["savePath"]);
//$obj->LoadDb();

?>