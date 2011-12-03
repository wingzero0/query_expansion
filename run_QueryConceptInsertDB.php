<?php
//sample usage:
//php run_QueryConceptInsertDB.php -c 1.txt -TB QueryCluster
require("/home/b95119/query_expansion/QueryConceptInsertDB.php");

$para = ParameterParser($argc, $argv);
$obj = new QueryConceptInsertDB($para);
$obj->run();
?>
