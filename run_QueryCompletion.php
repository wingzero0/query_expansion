<?php
//sample usage:
//php run_QueryCompletion.php -q1 "haha" -q2 "schwab ha文 s" -qTB QueryCluster_3
// -wTB WordCluster_3 -cFlowTB ClusterFlowProb_3 -llrTB llr_test_3
// -threshlod 0.0 -flowThreshold 0.01 -llrThreshold -0.1
// -alpha 0.5 -beta 0.3 gamma 0.1
require("/home/b95119/query_expansion/QueryCompletion.php");

$para = ParameterParser($argc, $argv);
$keys = array("q1", "q2", "qTB", "cFlowTB", "wTB", "llrTB",
	"threshlod", "flowThreshold", "llrThreshold","alpha", "beta", "gamma");
$ret = ParameterChecking($keys, $para);
$obj = new QueryCompletion($para["q1"], $para["q2"], $para["qTB"], 
	$para["wTB"], $para["cFlowTB"], $para["llrTB"], 
	$para["threshlod"], $para["flowThreshold"], $para["llrThreshold"],
	$para["alpha"], $para["beta"], $para["gamma"]);
$ret = $obj->GetQueryCombination();
print_r($ret);
?>
