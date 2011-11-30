<?php
require("/home/b95119/query_expansion/QueryConceptInsertDB.php");

$para = ParameterParser($argc, $argv);
$obj = new QueryConceptInsertDB($para);
$obj->run("schwab");
?>