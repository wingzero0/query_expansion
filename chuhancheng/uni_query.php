<?
// sample usage:
// php uni_query.php all_query_x.txt uniq_query_x.txt
//
if ($argc < 3){
	echo "sample usage:\nphp uni_query.php all_query_x.txt uniq_query_x.txt\n";
}
$fd = fopen($argv[1],'r');

$test = 0;
while($line = fgets($fd)){
	$line = trim($line);
	if(!isset($q_array[$line])){
		$q_array[$line] = 1;
	}
	else{
		$q_array[$line]++;
	}


	$test++;
	if($test%50000 == 0){
		echo $test."\n";
	}

}

fclose($fd);



$fw = fopen($argv[2],'w');

foreach($q_array as $key => $value){
	fwrite($fw,$value."\t".$key."\n");
}

fclose($fw);

?>
