<?php
 /*	this file will read the .txt file from argv[1] (pair_nqq.txt)
 		argv[2] should be QueryCluster table name.
		argv[3] should be cluster_pair table name. 
	*	.txt is the file which first colume is how many time <q1,q2> show up ,  second and third colume are q1 and q2;
	*	this program find out pair <q1,q2> belong to which <c1,c2> from table 'QueryCluster', 
	*	after that  check is pair <c1,c2> has existed in 'cluster_pair' table,
	*	finally, if pair has exist then let pair_value +1 ,otherwise insert pair to the table.
	* sample usage:
	* php mysql_dump2.php pair_nqq_4.txt QueryCluster_4 cluster_pair.test filter.txt keep.txt
 */
include_once("connect.php");
mysql_select_db($database_cnn,$r99922134_cnn);

$buf = 333;
if($argc<6){
	fprintf(STDERR,"please input query pair file in argv[1]\n");
	fprintf(STDERR,"Specify the QueryCluster table name with argv[2]\n");
	fprintf(STDERR,"Specify the cluster_pair table name with argv[3]\n");
	fprintf(STDERR,"Specify the filterOut(output) file name with argv[4]\n");
	fprintf(STDERR,"Specify the keep(output) file name with argv[5]\n");
}
$queryClusterTB = $argv[2];
$clusterPair = $argv[3];

$filterOut = $argv[4];
$filterFp = fopen($filterOut,"w");
if ($filterFp == NULL){
	fprintf(STDERR,"argv[4] can't be open\n");
	exit(-1);
}

$keep = $argv[5];
$keepFp = fopen($keep,"w");
if ($keepFp == NULL){
	fprintf(STDERR,"argv[5] can't be open\n");	
	exit(-1);
}

$fo = fopen($argv[1],"r");

// load QueryCluster table into memory
$sqr = "select `Query`,`ClusterNum` from $queryClusterTB ";
$result = mysql_query($sqr) or die( mysql_error() . " " . $sqr );
while ($row = mysql_fetch_row($result) ){
	$q = addslashes($row[0]);
	$c = intval($row[1]);
	$queryC[$q] = $c;
}

$flow = array();

while(!feof($fo)){
	$buf = fgets($fo);
	list($count,$tmpq1,$tmpq2) = split("[\t]",$buf);

	if (empty($count)){
		continue;
	}
	//echo $count."\n".$q1."\n".$q2."\n";
	//echo $buf."\n";
	$q1 = addslashes(trim($tmpq1));
	$q2 = addslashes(trim($tmpq2));
	if ( !isset($queryC[$q1]) ){
		fprintf($filterFp, "%d\t%s\t%s\n", $count,$q1,$q2);
		continue;
	}else{
		$t1 = $queryC[$q1];
	}
	if ( !isset($queryC[$q2]) ){
		fprintf($filterFp, "%d\t%s\t%s\n", $count,$q1,$q2);
		continue;
	}else{
		$t2 = $queryC[$q2];
	}
	
	fprintf($keepFp, "%d\t%s\t%s\n", $count,$q1,$q2);
	if ( !isset($flow[$t1][$t2])	){
		$flow[$t1][$t2] = 0;
	}
	$flow[$t1][$t2] += $count;

}
fclose($filterFp);
fclose($keepFp);
fclose($fo);
/*
foreach ($qairArray as $q1 => $v){
	foreach ($v as $q2 => $counter){
		if ($counter > 1){
			echo $q1."\t".$q2."\tcount".$counter."\n";
		}
	}
}
 */
//print_r($qairArray);
//exit(0);

//load cluster number into memory

/*
foreach ($qairArray as $q1 => $v){
	if ( !isset($queryC[$q1]) ){
		continue;
	}
	$t1 = $queryC[$q1];

	foreach ($v as $q2 => $counter){
		if ( !isset($queryC[$q2]) ){
			continue;
		}
		$t2 = $queryC[$q2];

		if ( !isset($flow[$t1][$t2])	){
			$flow[$t1][$t2] = 0;
		}
		$flow[$t1][$t2] += $counter;
	}
}*/


foreach ($flow as $t1 =>$v){
	foreach ($v as $t2 => $counter){
		$p_sqr = "insert into `$clusterPair` (`cluster1`,`cluster2`,`pair_value`) values( $t1 , $t2 , $counter )";
		mysql_query($p_sqr) or die( mysql_error() . " " . $sqr );

	}
}

?>
