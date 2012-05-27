<?php
require_once("/home/b95119/query_expansion/UserStudy.php");
require_once("/home/b95119/query_expansion/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

$systemPath = "/home/b95119/query_expansion/";

$file = "userStudy.txt";
$fp = fopen($file, "r");
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

$sql = sprintf(
	"SELECT  `R`.`qPairID` ,  `M`.`methodName` 
	FROM  `UserStudyRecord` AS  `R` 
	LEFT JOIN  `UserStudyMethod` AS  `M` ON  `R`.`methodID` =  `M`.`id` 
	AND  `R`.`user` =  '%s'
	GROUP BY  `R`.`qPairID` ,  `M`.`methodName`",
	$_GET["user"]);

$result = mysql_query($sql) or dir($sql."\n".mysql_error());

while($row = mysql_fetch_row($result) ){
	$rated[$row[0]][$row[1]] = true;
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
					if ( isset($rated[$i][$method[$j]]) ){
						$str = $i."(rated)";
					}else{
						$str = $i;
					}
					printf("<a href=\"%s\">%s</a><br/>", $v, $str);
				}
				echo '</div>';
			}
		?>	
	</body>
</html>	
