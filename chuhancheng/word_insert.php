<?php
include_once("connect.php");
mysql_select_db($database_cnn,$r99922134_cnn);

if ($argc < 3){
	fprintf(STDERR,"Specify the all_query.txt with argv[1]\n");
	fprintf(STDERR,"Specify the word_test table name with argv[2]\n");
	exit(-1);
}

$fd = fopen($argv[1],"r");

$test = 0;
while($line = fgets($fd)){
	$line = trim($line);
	$array = split("[ ]",$line);
	

	for($i=0;$i<sizeof($array);$i++){
		if(!isset($t_array[$array[$i]])){
			$t_array[$array[$i]] = 1;
		}
		else{
			$t_array[$array[$i]]++;
		}
	}
	$test++;
	if($test%100000==0){
		echo $test."\n";
		//break;
	}
}
fclose($fd);
//ksort($t_array);
//print_r($t_array);

//exit(-1);
$test = 0;
foreach($t_array as $key => $v){
	$test++;
	if($test%10000==0){
		echo $test."\n";
	}
	//echo $key."\t".$v."\n";
	$key = addslashes($key);
	//$v = addslashes($v);
	$sqr = sprintf("insert into `%s` (`word`,`value`) values('%s',%d)", 
		$argv[1], $key, $v);
	mysql_query($sqr) or die(mysql_error());
}


//var_dump($pair_array);
?>
