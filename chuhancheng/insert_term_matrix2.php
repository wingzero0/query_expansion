<?php
include_once("connect.php");
mysql_select_db($database_cnn,$r99922134_cnn);

if($argc<3){
	fprintf(STDERR,"please input query file (all_query.txt) in argv[1]\n");
	fprintf(STDERR,"please input matrix_table (matrix_test) name in argv[2]\n");
	exit(-1);
}

$test=0;
$fo = fopen($argv[1],"r");
$TB = $argv[2];
$query = fgets($fo);
while(!feof($fo)){
	$query = fgets($fo);
	$query = trim($query);
	$term_array = split(" ",$query);

	for($i=0;$i<sizeof($term_array)-1;$i++){
		//for($j=$i+1;$j<sizeof($term_array);$j++){
			//echo $term_array[$i]."\t".$term_array[$j]."\n";
			if(!isset($pair_array[$term_array[$i]][$term_array[$i+1]])){
				$pair_array[$term_array[$i]][$term_array[$i+1]] = 1;
			}
			else{
				$pair_array[$term_array[$i]][$term_array[$i+1]]++;
			}
		//}
	}


	$test++;
	if($test%50000 == 0){
		echo $test."\n";
	}

}
fclose($fo);


$test = 0;
foreach($pair_array as $key => $v){
	$test++;
	if($test%100000==0){
		echo $test."\n";
	}
	$term1 = $key;
	foreach($v as $key2 => $value){
		//echo $term1."\t".$key2."\t".$value."\n";
		$term1 = addslashes($term1);
		$key2 = addslashes($key2);
		//$value = addslashes($value);
		$sqr = "insert into $TB (w1,w2,value) values('$term1','$key2', $value )";
		mysql_query($sqr) or die(mysql_error());
	}
}


//var_dump($pair_array);
?>
