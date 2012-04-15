<?php
//sample usage:
//php run_DumpCompletionForMerge.php -t 0 -c 2 -input pair_nqq.txt -method completion -o out.txt
// only for method = completion

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
		continue;
		//$test_next = $next_query;
	}else if ( $characters >= strlen($next_term_array[$term_number]) ){
		continue;
	}else{
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
		$ret = run_QueryCompletion($f_query, $test_next, 5);
	}

	fwrite($fdout,$pair."\n");
	if (!empty($ret["completionProb"])){
		foreach($ret["completionProb"] as $key => $value){
			fwrite($fdout,"\t".$key."\t".$ret["concept"][$key]."\t".$value."\n");
		}
	}
}
fclose($fdout);
fclose($fd);
?>
