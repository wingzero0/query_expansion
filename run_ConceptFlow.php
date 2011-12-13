<?php
//sample usage:
//php run_ConceptFlow.php -oldTB cluster_pair -newTB ClusterFlowProb 
require("/home/b95119/query_expansion/ConceptFlow.php");

$para = ParameterParser($argc, $argv);
$obj = new ConceptFlow ($para);
$ret = $obj->run();
?>
