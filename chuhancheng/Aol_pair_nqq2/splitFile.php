<?php
// read the input file and split it into many small files with m lines in 
// each file.
//
// sample usege:
// php splitFile.php -i infile.txt -o outfileName -m mLine

include("/home/b95119/mylib/kit_lib.php");

$para = ParameterParser($argc, $argv);
$keys = array("i", "o", "m");
$ret = ParameterChecking($keys, $para);

$fp = fopenForRead($para["i"]);
if ($fp == null){
	return;
}
$lines = GetMLine($fp, $para["m"]);
$i = 1;
while (!empty($lines)){
	$fpw = fopenForWrite($para["o"].".".$i);
	WriteMLine($fpw, $lines);
	fclose($fpw);
	$lines = GetMLine($fp, $para["m"]);
	$i++;
}
fclose($fp);

function GetMLine($fp,$m){
	$i = 0;
	$lines = array();
	while ($i<$m && $line = fgets($fp)){
		$lines[] = $line;
		$i++;
	}
	return $lines;
}
function WriteMLine($fp, $lines){
	foreach ($lines as $line){
		fprintf($fp,"%s",$line);
	}
}
?>
