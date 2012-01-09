<?php
require("/home/b95119/query_expansion/QueryCompletionBaseline.php");

function run_Baseline($q1, $q2, $limit = 10){
	$para["q1"] = $q1;
	$para["q2"] = $q2;
	$para["qTB"] = "QueryCluster_4";
	$para["wTB"] = "WordCluster_4";
	$obj = new QueryCompletionBaseline($para["q1"], $para["q2"], $para["qTB"],
		$para["wTB"], 6, $limit);
	$ret = $obj->GetMostFreqQuery();
	return $ret; 
}
//$ret = run_Baseline("apple", "a");
//var_dump($ret);
?>
