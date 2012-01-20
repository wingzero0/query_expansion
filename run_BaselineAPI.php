<?php
require_once("/home/b95119/query_expansion/QueryCompletionBaseline.php");

function run_Baseline($q1, $q2, $DBVerNum,$limit = 10){
	$para["q1"] = $q1;
	$para["q2"] = trim($q2);
	$para["qTB"] = "QueryCluster_".$DBVerNum."_Clean";
	//$para["qTB"] = "QueryCluster_".$DBVerNum;
	$para["wTB"] = "WordCluster_".$DBVerNum;
	$obj = new QueryCompletionBaseline($para["q1"], $para["q2"], $para["qTB"],
		$para["wTB"], 6, $limit);
	$ret = $obj->GetMostFreqQuery();
	return $ret; 
}
function run_PairFreq($q1, $q2, $DBVerNum,$limit = 10){
	$para["q1"] = $q1;
	$para["q2"] = trim($q2);
	$para["qTB"] = "QueryCluster_".$DBVerNum."_Clean";//ignore
	$para["wTB"] = "WordCluster_".$DBVerNum;// ignore
	$obj = new QueryCompletionBaseline($para["q1"], $para["q2"], $para["qTB"],
		$para["wTB"], 0, $limit);
	$ret = $obj->GetMostFreqPair("pair_nqq_".$DBVerNum);
	return $ret; 
}
//$ret = run_Baseline("apple", "a");
//var_dump($ret);
?>
