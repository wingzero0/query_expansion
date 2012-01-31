<?php
require_once("connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

if($argc<3){
	fprintf(STDERR,"Specify the PairNqq table name with argv[1]\n");
	fprintf(STDERR,"Specify the SingleQ table name with argv[2]\n");
}
$pairNqqTB = $argv[1];
$singleQTB = $argv[2];

$sql = sprintf("select * from `%s`", $pairNqqTB);
$result = mysql_query($sql) or die( mysql_error() . "\n" . $sql ."\n");

$qs = array();
while($row = mysql_fetch_row($result)){
	$q = addslashes($row[1]);
	if ( !isset($qs[$q]) ){
		$qs[$q] = 0;
	}
	$qs[$q] += intval($row[3]);
	
	$q = addslashes($row[2]);
	if ( !isset($qs[$q]) ){
		$qs[$q] = 0;
	}
	$qs[$q] += intval($row[3]);
}

foreach ($qs as $safe_q => $v){
	$sql = sprintf(
		"INSERT INTO `%s` (`word`,`value`) 
		VALUES ('%s', %d);", 
		$singleQTB, $safe_q, $v
	);
	$result = mysql_query($sql) or die( mysql_error() . "\n" . $sql ."\n");
}

?>
