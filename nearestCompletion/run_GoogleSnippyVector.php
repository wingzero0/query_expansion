<?php
// sample usage:
// php run_GoogleSnippyVector.php -path googleHtml/

require_once(dirname(__FILE__)."/GoogleSnippyVector.php");

$para = ParameterParser($argc, $argv);

$keys = array("path");
$ret = ParameterChecking($keys, $para);

$obj = new GoogleSnippyVector();
$obj->PathFileHtmlToSnippy($para["path"]);

?>