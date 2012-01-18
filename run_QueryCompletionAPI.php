<?php
//sample usage:
//php run_QueryCompletion.php -q1 "haha" -q2 "schwab ha文 s"
require_once("/home/b95119/query_expansion/QueryCompletion.php");

function run_QueryCompletion($q1, $q2, $DBVerNum){
	$para["q1"] = $q1;
	$para["q2"] = $q2;
	$para["qTB"] = "QueryCluster_".$DBVerNum;
	$para["wTB"] = "WordCluster_".$DBVerNum;
	$para["qTBTight"] = "QueryCluster_".$DBVerNum."_Clean";
	$para["cFlowTB"] = "ClusterFlowProb_".$DBVerNum;
	$para["llrTB"] = "t_test_".$DBVerNum;
	$para["threshlod"] = "0.0";
	$para["flowThreshold"] = "0.000";
	$para["llrThreshold"] = "30.0";
	$para["alpha"] = "0.5";
	$para["beta"] = "0.3";
	$para["gamma"] = "0.2";
	$obj = new QueryCompletion($para["q1"], $para["q2"], $para["qTB"],
		$para["qTBTight"], $para["wTB"], $para["cFlowTB"], $para["llrTB"],
		$para["flowThreshold"],$para["threshlod"], $para["llrThreshold"],
		$para["alpha"], $para["beta"], $para["gamma"]);
	$ret = $obj->GetQueryCombination();
	return $ret; 
}

function run_QueryCompletionWithFlowAndFreq($q1, $q2, $DBVerNum){
	$para["q1"] = $q1;
	$para["q2"] = $q2;
	$para["qTB"] = "QueryCluster_".$DBVerNum;
	$para["wTB"] = "WordCluster_".$DBVerNum;
	$para["qTBTight"] = "QueryCluster_".$DBVerNum."_Clean";
	$para["cFlowTB"] = "ClusterFlowProb_".$DBVerNum;
	$para["llrTB"] = "t_test_".$DBVerNum;
	$para["threshlod"] = "0.0";
	$para["flowThreshold"] = "0.000";
	$para["llrThreshold"] = "30.0";
	$para["alpha"] = "0.5";
	$para["beta"] = "0.3";
	$para["gamma"] = "0.2";
	$obj = new QueryCompletion($para["q1"], $para["q2"], $para["qTB"],
		$para["qTBTight"], $para["wTB"], $para["cFlowTB"], $para["llrTB"],
		$para["flowThreshold"],$para["threshlod"], $para["llrThreshold"],
		$para["alpha"], $para["beta"], $para["gamma"]);
	$ret = $obj->GetQueryCombinationWithOtherMethod();
	//print_r($ret);
	return $ret; 
}
?>
