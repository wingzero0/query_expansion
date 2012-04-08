<?php
// sample usage:
// php run_RecommendationTree.php -qTb RelatedQuery -nTb AolNgramTree -vTb AolNgramVector
// php run_RecommendationTree.php -qFile RelatedQuery -vFile AolNgramVector
require(dirname(__FILE__)."/RecommendationTree.php");

$para = ParameterParser($argc, $argv);

//$keys = array("qTb", "nTb", "vTb");
//$keys = array("qFile", "vTb");
$keys = array("vFile", "vTb");
$ret = ParameterChecking($keys, $para);

$obj = new RecommendationTree();
/*
//$rq = $obj->LoadRelatedQDB($para["qTb"]);
$rq = $obj->LoadRelatedQFile($para["qFile"]);

$trees = $obj->ConstructTrees($rq, 3);
echo "trees construct complete\n";
//print_r($trees);
//$obj->SaveTreeNgram($trees);
//echo "trees saving complete\n";

$idf = $obj->NgramIDF(2, $para["qFile"]); // not load from Database, use the tree in memory directly
echo "idf complete:\n";
//print_r($idf);


$vector = $obj->ConstructVector($idf, $trees);
echo "vector complete\n";
//print_r($vector);
$obj->SaveVectorDB($vector, $para["vTb"]);
//$obj->SaveVectorFile($vector, $para["vFile"]);
echo "saving vector complete\n";
*/

$obj->SaveVectorFromFileToDB($para["vFile"], $para["vTb"]);
?>