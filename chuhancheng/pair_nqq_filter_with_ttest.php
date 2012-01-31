<?php
require("/home/b95119/query_expansion/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

if ($argc<5){
	printf( "usage:php %s output upperThreshold lowerThreshold tTestTableName probTableName\n", basename(__FILE__));
	//printf( "usage:php %s output upperThreshold lowerThreshold tTestTableName probTableName flowTableName clusterTbName\n", basename(__FILE__));
	exit(-1);
}
$pair_nqq_out = $argv[1]; // output file name
$upper = doubleval($argv[2]); // upper bound threshold for the selecting freq
$lower = doubleval($argv[3]); // lower bound threshold for the selecting freq
$ttestTbName = $argv[4]; // t-test table name
$probTbName = $argv[5]; // prob table name
//$flowTbName = $argv[6]; // flow table name
//$clusterTbName = $argv[7];


// get with prob
$sql = sprintf(
	"select `Word1`,`Word2`,`Prob` from `%s` 
	where `Word1` != `Word2` and `Prob` >= 0.05
	order by `Prob` desc",
	$probTbName 
);


$result = mysql_query($sql) or die($sql."\n".mysql_error());
while($row = mysql_fetch_row($result)){
	$f_query = $row[0]; // q1
	$next_query = $row[1]; // q2
	$prob[$f_query][$next_query] = doubleval($row[2]);
}

// get cluster
$sql = sprintf("select `Query`,`ClusterNum` from `QueryCluster_5`");

$result = mysql_query($sql) or die($sql."\n".mysql_error());
while($row = mysql_fetch_row($result)){
	$q = $row[0]; // query
	$c = $row[1]; // cluster
	$qCluster[$q] = $c;
}

// get cluster flow prob
$sql = sprintf("select `Cluster1`,`Cluster2`, `Prob` from `ClusterFlowProb_5`");

$result = mysql_query($sql) or die($sql."\n".mysql_error());
while($row = mysql_fetch_row($result)){
	$c1 = $row[0];
	$c2 = $row[1];
	$cFlow[$c1][$c2] = doubleval($row[2]);
}

// get with t-value
$sql = sprintf(
	"select `Word1`,`Word2`,`TValue` from `%s` where `Word1` != `Word2` and `TValue` > %lf and `TValue` <= %lf order by `TValue` desc",
	$ttestTbName, $lower, $upper
);

$result = mysql_query($sql) or die($sql."\n".mysql_error());
echo "num:".mysql_num_rows($result)."\n";

$fpout = fopen($pair_nqq_out,"w");

while($row = mysql_fetch_row($result)){
	//echo $row[0]."\t".$row[1]."\t".$row[2]."\n";
	$f_query = $row[0]; // q1
	$next_query = $row[1]; // q2
	
	/*
	if ( !isset($prob[$f_query][$next_query]) || 
		!isset($qCluster[$f_query]) || !isset($qCluster[$next_query]) ||
		!isset( $cFlow[$qCluster[$f_query]][$qCluster[$next_query]] )
	){
		continue;
	}
	 */
	if ( !isset($qCluster[$f_query]) || !isset($qCluster[$next_query]) ||
		!isset( $cFlow[$qCluster[$f_query]][$qCluster[$next_query]] )
	){
		continue;
	}
	$Pattern = "/(www\s*)|(\s*com)/";
	$q1 = preg_replace($Pattern, "", $f_query );
	$q2 = preg_replace($Pattern, "", $next_query );
	if (levenshtein( $q1 , $q2 ) <= 2 
		|| strlen($q1) == 0 || strlen($q2) == 0){ // drop the pair
			//echo $q1."\t".$q2."\n";
			continue;
		}
	$output_array[$f_query][$next_query] = doubleval($row[2]);
}

foreach ($output_array as $q1 => $v){
	foreach ($v as $q2 => $tvalue){
		fwrite($fpout,$tvalue."\t".$q1."\t".$q2."\n");
	}
}

fclose($fpout);
?>
