<?php
//sample usage:
// php run_QueryConceptTypoFilter.php -oTB QueryCluster_5 -nTB QueryCluster_5_Clean


require("/home/b95119/query_expansion/QueryConceptTypoFilter.php");

$para = ParameterParser($argc, $argv);
$keys = array("oTB", "nTB");
$ret = ParameterChecking($keys, $para);
$obj = new QueryConceptTypoFilter($para["oTB"], $para["nTB"]);
$obj->LoadDB();
$obj->CleanQuery();


?>
