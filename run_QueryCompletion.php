<?php
//sample usage:
//php run_QueryCompletion.php -q1 "haha" -q2 "schwab ha文 s" -qTB QueryClusterTest 
// -cFlowTB ClusterFlowProb -llrTB llr_test_2 
require("/home/b95119/query_expansion/QueryCompletion.php");

$para = ParameterParser($argc, $argv);
$keys = array("q1", "q2", "qTB", "cFlowTB", "wTB", "llrTB");
$ret = ParameterChecking($keys, $para);
$obj = new QueryCompletion($para["q1"], $para["q2"], $para["qTB"], 
	$para["wTB"], $para["cFlowTB"], $para["llrTB"]);
$ret = $obj->GetQueryCombination();
print_r($ret);
?>
