<?php
//sample usage:
//php run_Recommendation.php -t 0 -c 2 -input pair_nqq.txt -method completion -o out.txt

require_once("/home/b95119/query_expansion/run_QueryCompletionAPI.php");
require_once("/home/b95119/query_expansion/run_BaselineAPI.php");

$para = ParameterParser($argc, $argv);
$term_number = intval($para["t"]); // the number of complete term
$characters = intval($para["c"]); // the number of character is the partial term

$fd = fopen($para["input"], "r");
$fdout = fopen($para["o"], "w");

while($pair = fgets($fd)){
	$pair = trim($pair);
	$pair_array = explode("\t",$pair);
	$num = intval($pair_array[0]); // the number of pair appears
	$f_query = trim($pair_array[1]); // q1
	$next_query = trim($pair_array[2]); // q2

	$next_term_array = explode(" ",$next_query);

	if($term_number >= count($next_term_array)){
		$test_next = $next_query;
	}
	else{
		$test_next="";
		for($i=0; $i<$term_number;$i++){
			$test_next = $test_next." ".$next_term_array[$i];
		}
		$test_next = trim($test_next);
		//echo $test_next."\n";
		$test_next = $test_next." ".substr($next_term_array[$term_number],0,$characters);
		$test_next = trim($test_next);
	}

	if ($para["method"] == "completion"){
		//echo $pair."\n";
		//echo $f_query."\t".$test_next."\n";
		$ret = run_QueryCompletion($f_query, $test_next, 5);
	}else if ($para["method"] == "baseline"){
		$ret = run_Baseline($f_query, $test_next, 5);
	}else if ($para["method"] == "flowandfreq"){
		//echo $f_query."\n";
		//echo $test_next."\n";
		
		$ret = run_QueryCompletionWithFlowAndFreq($f_query, $test_next, 5);
	}
	
	fwrite($fdout,$pair."\n");
	$i = 0;
	foreach($ret as $key => $value){
		fwrite($fdout,"\t".$key."\n");
		$i++;
		if ($i >= 10){
			break;
		}
	}
}
fclose($fdout);
fclose($fd);
?>
