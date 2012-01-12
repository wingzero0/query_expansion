<?php
require("/home/b95119/query_expansion/run_QueryCompletionAPI.php");

if ($argc<6){
	printf( "usage:php %s term_number char_number sessionFile outputdir DBVersionNum\n", basename(__FILE__));
	exit(-1);
}
$term_number = $argv[1];
$characters = $argv[2];
$testSession = $argv[3];
$outputDir = $argv[4];
$dbNum = intval($argv[5]);

$fd = fopen($testSession,"r");
$fdwm = fopen($outputDir."/match_" . $term_number . "_" . $characters. ".txt","w");
$fdwn = fopen($outputDir."/nonmatch_" . $term_number . "_" . $characters. ".txt","w");
$fderror = fopen($outputDir."/error_" . $term_number . "_" . $characters. ".txt","w");


$score = 0;
$datasize = 0;
while($session = fgets($fd)){
	$session = trim($session);
	$session_array = explode("\t",$session);
	$uid = $session_array[0];
	$flow_count = $session_array[1];
	$f_query = $session_array[3];
	$next_query = $session_array[5];
	
	$query_flow = array();
	for($i=0;$i<$flow_count-1;$i++){
		$query_flow[$session_array[$i*2+5]] = true;
		//echo $session_array[$i*2+5]."\n";
	}

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

	$ret = run_QueryCompletion($f_query, $test_next, $dbNum);
	//echo $f_query."\t".$test_next."\n";
	//var_dump($ret);

	$test = 0;
	$nonmatch = true;
	if(empty($ret)){
		fwrite($fderror,$session."\n");
		fwrite($fderror,$f_query."\t".$test_next."\n");
		$nonmatch = false;
	}

	foreach($ret as $key => $value){
		//echo $key."\t"."$value"."\n";
		if(isset($query_flow[$key])){
			$nonmatch = false;
			fwrite($fdwm,$session."\n");
			fwrite($fdwm,$f_query."\t".$key."\n");
			//echo $f_query."\t".$key."\n";
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

	//echo "score :".$score."\n";


	$datasize++;
	//echo "datasize = ".$datasize."\n";
	if($datasize % 100 == 0){ 
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
