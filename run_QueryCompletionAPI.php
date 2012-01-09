<?php
//sample usage:
//php run_QueryCompletion.php -q1 "haha" -q2 "schwab ha文 s"
require("/home/b95119/query_expansion/QueryCompletion2.php");

function run_QueryCompletion($q1, $q2){
	$para["q1"] = $q1;
	$para["q2"] = $q2;
	$para["qTB"] = "QueryCluster_4";
	$para["wTB"] = "WordCluster_4";
	$para["qTBTight"] = "QueryCluster_4_Clean";
	$para["cFlowTB"] = "ClusterFlowProb_4";
	$para["llrTB"] = "llr_test_4";
	$para["threshlod"] = "0.0";
	$para["flowThreshold"] = "0.005";
	$para["llrThreshold"] = "-30.0";
	$para["alpha"] = "0.5";
	$para["beta"] = "0.3";
	$para["gamma"] = "0.2";
	$obj = new QueryCompletion($para["q1"], $para["q2"], $para["qTB"],
		$para["qTBTight"], $para["wTB"], $para["cFlowTB"], $para["llrTB"],
		$para["threshlod"], $para["flowThreshold"], $para["llrThreshold"],
		$para["alpha"], $para["beta"], $para["gamma"]);
	$ret = $obj->GetQueryCombination();
	return $ret; 
}
?>
