<?php
// read uniq_query.txt and count the total number of query appeared
// sample usage:
// php query_count.php uniq_query.txt
// uniq_query.txt may be in /home/chuhancheng/project_ir/work_3/uniq_query.txt

$fp = fopen($argv[1], "r");
if ( $fp == NULL ){
	fprintf(STDERR, "%s can't be opened\n",$argv[1]);
}

$counter = 0;
while(!feof($fp)){
	$line = fgets($fp);
	$list = split("\t",$line);
	$counter += intval($list[0]);
}
fclose($fp);
echo $counter."\n";
?>
