<?php
//sample usage:
//php run_QueryConceptNumOfQuery.php -s session_qflow.txt -TB QueryCluster
require("/home/b95119/query_expansion/QueryConceptInsertDB.php");

$para = ParameterParser($argc, $argv);
$obj = new QueryConceptNumOfQuery($para);
$obj->Run();
?>
