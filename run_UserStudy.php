<?php
require_once("UserStudy.php");

$obj = new UserStudy("continental","american airlines", "./snippyPath/", "./resultPath/","./chuhancheng/tmpOutput/");
$ret = $obj->GetQSnippy("continental");
print_r($ret);
//$ret = $obj->GetCompletion("completionEntropy", 0, 1);
//print_r($ret);
for ($t = 0;$t<=1;$t++){
	for ($c = 1;$c <=3;$c++){
		$partialQ2 = $obj->GeneratePartialQ2($t,$c);
		echo $partialQ2."\n";
		$ret = $obj->GetCompletion("completionEntropy", $t, $c);
		print_r($ret);
	}
}
?>
