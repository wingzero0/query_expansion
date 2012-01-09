<?php
//sample usage:
// php run_QueryCompletion.php -q1 "google" -q2 "m" -qTB QueryCluster_4 
// -wTB WordCluster_4 -cFlowTB ClusterFlowProb_4 
// -llrTB llr_test_4 -threshlod 0.0 -flowThreshold 0.005 -llrThreshold -30.0 
// -alpha 0.5 -beta 0.3 -gamma 0.2
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
//$ret = $
print_r($ret);
?>
