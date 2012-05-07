<?php
require_once("/home/b95119/query_expansion/UserStudy.php");
$systemPath = "/home/b95119/query_expansion/";

$file = "userStudy.txt";
$fp = fopen("userStudy.txt", "r");
if ($fp == null){
	printf("%s can not open\n", $file);
	die();
}else {
	$method[0] = "completionEntropy";
	$method[1] = "baseline";
	$method[2] = "nearestHybrid";
	$i = 0;
	while ( $line = fgets($fp) ){
		$line = trim($line);
		$list = preg_split("/\t/", $line);
		$q1 = $list[1];
		$q2 = $list[2];
		$link[0][] = sprintf("rate.php?user=%s&qPairID=%d&q1=%s&q2=%s&methodName=%s", $_GET["user"],$i,$q1,$q2,$method[0]);
		$link[1][] = sprintf("rate.php?user=%s&qPairID=%d&q1=%s&q2=%s&methodName=%s", $_GET["user"],$i,$q1,$q2,$method[1]);
		$link[2][] = sprintf("rate.php?user=%s&qPairID=%d&q1=%s&q2=%s&methodName=%s", $_GET["user"],$i,$q1,$q2,$method[2]);
		$i++;
	}
	fclose($fp);
}
?>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="generateLink.css" type="text/css" />
		<title>User Study</title>		
	</head>
	<body>
		<?php
			for ($j = 0;$j<3;$j++){
				echo '<div align="center" style="float:left;width:150px;border:1px solid">';
				echo $method[$j]."<br/>";
				foreach($link[$j] as $i => $v){
					printf("<a href=\"%s\">%d</a><br/>", $v, $i);
				}
				echo '</div>';
			}
		?>	
	</body>
</html>	