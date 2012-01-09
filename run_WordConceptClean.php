<?php
//sample usage:
//php run_WordConceptClean.php -qTB QueryClusterClean -wTB WordCluster
require("/home/b95119/query_expansion/WordConceptInsertDB.php");

$para = ParameterParser($argc, $argv);
$keys = array("qTB","wTB");
$ret = ParameterChecking($keys, $para);
$obj = new WordConceptClean($para["qTB"], $para["wTB"]);
$obj->run();
?>
