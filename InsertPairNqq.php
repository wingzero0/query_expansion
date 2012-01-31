<?php
require_once("connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

if($argc<4){
	fprintf(STDERR,"please input query pair file in argv[1]\n");
	fprintf(STDERR,"Specify the QueryCluster table name with argv[2]\n");
	fprintf(STDERR,"Specify the PairNqq table name with argv[3]\n");
}
$pairFile = $argv[1];
$queryClusterTB = $argv[2];
$pairNqqTB = $argv[3];

$fp = fopen($pairFile,"r");
if ($fp == NULL){
	fprintf(STDERR,"argv[1] can't be open\n");
	exit(-1);
}

$sql = sprintf("select `Query` from `%s`", $queryClusterTB);
$result = mysql_query($sql) or die( mysql_error() . "\n" . $sql ."\n");

while($row = mysql_fetch_row($result)){
	$qs[$row[0]] = true;
}

while ($pair = fgets($fp)){
	$pair = trim($pair);
	$pair_array = explode("\t",$pair);
	$num = intval($pair_array[0]); // the number of pair appears
	$q1 = trim($pair_array[1]);
	$q2 = trim($pair_array[2]);
	//if ( isset($qs[$q1]) && isset($qs[$q2]) ){
	if ( isset($qs[$q1]) ){// for aol pair nqq 
		$sql = sprintf(
			"INSERT INTO `%s` (`q1`, `q2`, `pair_value`) 
			VALUES ('%s', '%s', %d);", 
			$pairNqqTB, addslashes($q1), addslashes($q2), $num 
		);
		$result2 = mysql_query($sql) or die( mysql_error() . "\n" . $sql ."\n");
	}
}
fclose($fp);
?>
