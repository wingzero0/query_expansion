<?php

function GetFileRecords($filename){
	$fp = fopen($filename, "r");
	if ($fp == null){
		echo $filename." can't be open<br>";
		return array();
	}
	$rates = array();
	while ($line = fgets($fp)){
		$list = preg_split("/\t/", $line);
		if ( count($list) != 2) {
			echo "formate error:".$line."\n";
			continue;
		}
		$rates[ intval($list[0]) ] = doubleval($list[1]);
	}
	fclose($fp);
	return $rates;
}

function GetRecords($dirname){
	$methods = array("completionPure", "baseline", "pairandfreq", 
		"flowandfreq", "nearestPure", "completionEntropy", 
		"nearestHybrid");
	
	$rates = array();
	foreach ($methods as $i => $name){
		$filename = $dirname."/".$name."_all.txt";
		$rates[$name] = GetFileRecords($filename);
	}
	return $rates;
}

$rates = GetRecords($_GET["dirname"]);
$methodsTitle = array("completionPure" => "ConceptCompletion", 
	"baseline" => "Pure Frequency", 
	"pairandfreq" => "Pair Frequency", 
	"flowandfreq" => "Concept Frequnecy", 
	"nearestPure" => "Nearest",
	"completionEntropy" => "ConceptCompletion + entropy",
	"nearestHybrid" => "NearestHybrid"
);
?>

<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Highcharts Example</title>
		
		
		<!-- 1. Add these JavaScript inclusions in the head of your page -->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
		<script type="text/javascript" src="../js/highcharts.js"></script>
		
		<!-- 1a) Optional: add a theme file -->
		<!--
			<script type="text/javascript" src="../js/themes/gray.js"></script>
		-->
		
		<!-- 1b) Optional: the exporting module -->
		<script type="text/javascript" src="../js/modules/exporting.js"></script>
		
		
		<!-- 2. Add the JavaScript to initialize the chart on document ready -->
		<script type="text/javascript">
		
			var chart;
			$(document).ready(function() {
				chart = new Highcharts.Chart({
					chart: {
						renderTo: 'container',
						defaultSeriesType: 'line',
						width: 800,
						marginRight: 300,
						marginBottom: 50
					},
					title: {
						text: ' InclustionRate ',
						x: -20 //center
					},
					subtitle: {
						text: ' <?php echo $_GET["dirname"] ?>',
						x: -20
					},
					xAxis: {
						categories: [
						<?php
							for ($i = 1;$i<=10;$i++){
								echo "'$i',";
							}
							//echo "'10',";
						?>],
						title: {
							enable: true,
							text: 'Rank',
						},
					},
					yAxis: {
						min: 0,
						title: {
							text: 'Inclusion Rate'
						},
						plotLines: [{
							value: 0,
							width: 1,
							color: '#808080'
						}]
					},
					tooltip: {
						formatter: function() {
				                return '<b>'+ this.series.name +'</b><br/>'+
								this.x +': '+ this.y +'';
						}
					},
					legend: {
						layout: 'vertical',
						align: 'right',
						verticalAlign: 'top',
						//x: -10,
						itemWidth: 300,
						y: 100,
						borderWidth: 0
					},
					series: [
						<?php
							foreach ($rates as $method => $row){
						?>
						{
						name: '<?php echo $methodsTitle[$method] ?>',
						data: [
							<?php 
								for ($i = 1;$i<= 10;$i++){
									echo $row[$i].",";
								}
								//echo $row[10];
							?>]
						},
						<?php
							}
						?>					
					]
				});
				
				
			});
				
		</script>
		
	</head>
	<body>
		
		<!-- 3. Add the container -->
		<div id="container" style="width: 800px; height: 400px; margin: 0 auto"></div>
		
				
	</body>
</html>	
