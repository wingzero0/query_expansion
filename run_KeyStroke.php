<?php
// read the input file and count the number of keystroke 
// when user use query completion system.
// sample usage
// php run_KeyStroke.php -i queryPair.txt -o num.txt -m method

require_once("KeyStroke.php");
require_once("/home/b95119/mylib/kit_lib.php");


$para = ParameterParser($argc, $argv);
$keys = array("i", "o", "m");
ParameterChecking($keys,$para);

$fp = fopen($para["i"], "r");
$fpOut = fopen($para["o"], "w");

if ($fp == null || $fpOut == null){
	fprintf(STDERR, "%s or %s can't be opened\n",$para["i"], $para["o"]);
}

$pattern = "/\t/";

while ( $line = fgets($fp) ){
	$line = trim($line);
	$list = preg_split($pattern, $line);
	$obj = new KeyStroke($list[1],$list[2], $para["m"]);
	$keyCount = $obj->SimulateUserTyping();
	fprintf($fpOut, "%d\t%s\t%s\t%d\t%d\n",
		$list[0],$list[1],$list[2],
		$keyCount["typing"],$keyCount["selection"]
	);
}

fclose($fp);
fclose($fpOut);

?>