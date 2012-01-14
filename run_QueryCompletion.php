<?php
//sample usage:
// php run_QueryCompletion.php -q1 "google" -q2 "m" -qTB QueryCluster_4 
// -qTBTight QueryCluster_4_Clean
// -wTB WordCluster_4 -cFlowTB ClusterFlowProb_4 
// -llrTB t_test_4 -threshlod 0.0 -flowThreshold 0.005 -llrThreshold 30.0 
// -alpha 0.5 -beta 0.3 -gamma 0.2
require("/home/b95119/query_expansion/QueryCompletion2.php");

$para = ParameterParser($argc, $argv);
$keys = array("q1", "q2", "qTB", "qTBTight",  "cFlowTB", "wTB", "llrTB",
	"threshlod", "flowThreshold", "llrThreshold","alpha", "beta", "gamma");
$ret = ParameterChecking($keys, $para);
$obj = new QueryCompletion($para["q1"], $para["q2"], $para["qTB"],$para["qTBTight"], 
	$para["wTB"], $para["cFlowTB"], $para["llrTB"], 
	$para["flowThreshold"], $para["threshlod"], $para["llrThreshold"],
	$para["alpha"], $para["beta"], $para["gamma"]);
$ret = $obj->GetQueryCombination();
print_r($ret);
?>
