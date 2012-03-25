<?php
// sample usage:
// php run_RecommendationTree.php -qTb RelatedQuery -nTb AolNgramTree -vTb AolNgramVector

require(dirname(__FILE__)."/RecommendationTree.php");

$para = ParameterParser($argc, $argv);

$keys = array("qTb", "nTb", "vTb");
$ret = ParameterChecking($keys, $para);

$obj = new RecommendationTree($para["qTb"],$para["nTb"],$para["vTb"]);
$rq = $obj->LoadRelatedQ();
$trees = $obj->ConstructTrees($rq, 2);
echo "trees construct complete\n";
//print_r($trees);
//$obj->SaveTreeNgram($trees);
//echo "trees saving complete\n";
$idf = $obj->NgramIDF(2); // not load from Database, use the tree in memory directly
echo "idf complete:\n";
//print_r($idf);
$vector = $obj->ConstructVector($idf, $trees);
echo "vector complete\n";
//print_r($vector);
$obj->SaveVector($vector);
echo "saving vector complete\n";
?>