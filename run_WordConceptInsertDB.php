<?php
//sample usage:
//php run_WordConceptInsertDB.php -c 1.txt -TB WordCluster
require("/home/b95119/query_expansion/WordConceptInsertDB.php");

$para = ParameterParser($argc, $argv);
$obj = new WordConceptInsertDB($para);
$obj->run();
?>
