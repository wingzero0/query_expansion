<?php
require("/home/b95119/query_expansion/run_QueryCompletionAPI.php");
require("/home/b95119/query_expansion/run_BaselineAPI.php");

if ($argc<7){
	printf( "usage:php %s term_number char_number method input outputdir DBVersionNum\n", basename(__FILE__));
	exit(-1);
}
$term_number = $argv[1]; // the number of complete term
$characters = $argv[2]; // the number of character is the partial term
$method = $argv[3]; // QueryCompletion or Baseline
$pair_nqq = $argv[4]; // input file name
$outputDir = $argv[5]; // output path
$dbNum = intval($argv[6]); // training data version


$fd = fopen($pair_nqq,"r");
$fdwm = fopen($outputDir."/match_" . $term_number . "_" . $characters. ".txt","w");
$fdwn = fopen($outputDir."/nonmatch_" . $term_number . "_" . $characters. ".txt","w");
$fderror = fopen($outputDir."/error_" . $term_number . "_" . $characters. ".txt","w");


$score = 0;
$datasize = 0;
while($session = fgets($fd)){
	$session = trim($session);
	$session_array = explode("\t",$session);
	$num = intval($session_array[0]); // the number of pair appears
	$f_query = trim($session_array[1]); // q1
	$next_query = trim($session_array[2]); // q2
	
	$query_flow = array();
	$query_flow[$next_query] = true;

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
	}

	$test_next = trim($test_next);
	//echo $test_next."\n"; //qt
	if ($method == "completion"){ 
		$ret = run_QueryCompletion($f_query, $test_next, $dbNum);
	}else if ($method == "baseline"){
		$ret = run_Baseline($f_query, $test_next, $dbNum);
	}
	//echo $f_query."\t".$test_next."\n";
	//var_dump($ret);

	$test = 0;
	$nonmatch = true;
	if(empty($ret)){
		fwrite($fderror,$session."\n");
		//fwrite($fderror,$f_query."\t".$test_next."\n");
		//continue;
		$nonmatch = false;
	}

	foreach($ret as $key => $value){
		//echo $key."\t"."$value"."\n";
		if(isset($query_flow[$key])){
			$nonmatch = false;
			fwrite($fdwm,$session."\n");
			//fwrite($fdwm,$f_query."\t".$key."\n");
			$score++;
			
			$ttt = 0;
			foreach($ret as $key => $value){
				fwrite($fdwm,"\t".$key."\n");
				$ttt++;
				if($ttt>=10) break;
			}
			break;
		}
		$test++;
		if($test >= 10) break; 
	}

	if($nonmatch){
		fwrite($fdwn,$session."\n");
		$ttt = 0;
		foreach($ret as $key => $value){
			fwrite($fdwn,"\t".$key."\n");
			$ttt++;
			if($ttt>=10) break;
		}
	}
	
	$datasize++;
	if($datasize % 10 == 0){ 
		//break; 
		echo "score :".$score."\n";
		echo "datasize = ".$datasize."\n";
	}
}
echo "score :".$score."\n";

fclose($fd);
fclose($fdwm);
fclose($fdwn);
fclose($fderror);
?>
