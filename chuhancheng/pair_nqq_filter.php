<?php
require("/home/b95119/query_expansion/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

if ($argc<5){
	printf( "usage:php %s input output threshold DBVersionNum\n", basename(__FILE__));
	exit(-1);
}
$pair_nqq = $argv[1]; // input file name
$pair_nqq_out = $argv[2]; // output file name
$threshold = $argv[3]; // threshold for the selecting freq
$tbName = $argv[4]; // training data version


$fpin = fopen($pair_nqq,"r");
$fpout = fopen($pair_nqq_out,"w");
$fperr = fopen($pair_nqq_out."_err.txt","w");

$score = 0;
$datasize = 0;
while($pair = fgets($fpin)){
	$pair = trim($pair);
	$pair_array = explode("\t",$pair);
	$num = intval($pair_array[0]); // the number of pair appears
	$f_query = trim($pair_array[1]); // q1
	$next_query = trim($pair_array[2]); // q2
	
	if ($num < $threshold){ // drop the pair
		continue;
	}else if ($f_query == $next_query){
		continue;
	}
	
	$q = addslashes($f_query);
	$sql = sprintf(
		"select * from `%s` where `Query` = '%s'",
		$tbName, $q
	);
	
	$result = mysql_query($sql) or die($sql."\n".mysql_error());
	
	if (mysql_num_rows($result)>0){ // save the pair
		$output_array[$pair] = $num; 
	}else{
		continue; // drop the pair if q1 is not in any concept
	}
	$datasize++;
	if($datasize % 100 == 0){ 
		echo "datasize = ".$datasize."\n";
	}
}

arsort($output_array);

foreach ($output_array as $pair => $num){
	fwrite($fpout,$pair."\n");
}

fclose($fpin);
fclose($fpout);
fclose($fperr);
?>
