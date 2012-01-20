<?php
// sample usage:
// php run_PartialQueryPairFilter.php -t 0 -c 1 -inPath ./Aol_pair_nqq2/ -outPath ./Aol_pair_nqq_0_1/
require_once("/home/b95119/query_expansion/ParialQueryEntropy.php");
require_once("/home/b95119/mylib/kit_lib.php");

$para = ParameterParser($argc, $argv);
$term_number = intval($para["t"]); // the number of complete term
$characters = intval($para["c"]); // the number of character is the partial term
$inPath = $para["inPath"];
$outPath = $para["outPath"];

$obj = new PartialQueryPairFilter("QueryCluster_5_Clean");
for ($i= 100;$i>=10;$i-=10){
	$infile = $inPath."/Aol_pair_nqq_" . $i . ".txt";
	$outHfile = $outPath."/Aol_pair_nqq_" . $i . ".hight.txt";
	$outLfile = $outPath."/Aol_pair_nqq_" . $i . ".low.txt"; 	
	$obj->SetTermCharacter($term_number, $characters);
	$obj->Filter($infile, $outHfile, $outLfile, 5.0); // the final number is the entropy threshold
}


?>