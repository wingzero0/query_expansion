<?php
//sample usage:
//php RecommendationStatistics.php -input pair_nqq.txt -o out.txt
require_once("/home/b95119/mylib/kit_lib.php");

$para = ParameterParser($argc, $argv);

$fd = fopen($para["input"], "r");
$fdout = fopen($para["o"], "w");

for ($term_number = 0; $term_number < 10;$term_number++){
	for ($characters = 1; $characters < 20;$characters++){
		$counter[$term_number][$characters] = 0;
	}
}

while($pair = fgets($fd)){
	$pair = trim($pair);
	$pair_array = explode("\t",$pair);
	$num = intval($pair_array[0]); // the number of pair appears
	$f_query = trim($pair_array[1]); // q1
	$next_query = trim($pair_array[2]); // q2

	$next_term_array = explode(" ",$next_query);

	for ($term_number = 0; $term_number < 10;$term_number++){
		for ($characters = 1; $characters < 20;$characters++){

			if($term_number >= count($next_term_array)){
				continue;
				//$test_next = $next_query;
			}else if ( $characters >= strlen($next_term_array[$term_number]) ){
				continue;
			}else{
				$counter[$term_number][$characters]++;
			}
		}
	}
}
fclose($fd);

for ($term_number = 0; $term_number < 10;$term_number++){
	for ($characters = 1; $characters < 20;$characters++){
		fprintf($fdout, "valid data\t%d_%d\t%d\n",
			$term_number, $characters, $counter[$term_number][$characters]);
	}
}
fclose($fdout);
?>
