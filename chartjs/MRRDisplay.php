<?php

function GetFileRecords($filename){
	$fp = fopen($filename, "r");
	if ($fp == null){
		echo $filename." can't be open<br>";
		return array();
	}
	$rate = 0.0;
	while ($line = fgets($fp)){
		$rate = doubleval($line);
	}
	fclose($fp);
	return $rate;
}

function GetRecords($dirnamePrefix){
	$methods = array("completionPure", "baseline", "pairandfreq",
			"flowandfreq", "nearestPure", "completionEntropy",
			"nearestHybrid");

	$rates = array();
	foreach($methods as $method){
		for ($t = 0; $t <=3; $t++){
			for ($c = 1; $c<=19; $c++){
				$filename = $dirnamePrefix.$t."_".$c."/".$method.".txt";
				$rates[$method][$t."_".$c] = GetFileRecords($filename);
			}
		}
	}
	return $rates;
}

$rates = GetRecords($_GET["dirnamePrefix"]);
$methodsTitle = array("completionPure" => "Concept Completion",
		"baseline" => "Pure Frequency",
		"pairandfreq" => "Pair Frequency",
		"flowandfreq" => "Concept Frequnecy",
		"nearestPure" => "Nearest Completion",
		"completionEntropy" => "Concept Completion + entropy",
		"nearestHybrid" => "Nearest Hybrid Completion"
);

$title["ttest_"] = "Dependent data";
$title["independent_"] = "Independent data";
$title[""] = "All data";
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
						width:1000,
						marginRight: 300,
						marginBottom: 50
					},
					title: {
						text: ' MRR ',
						x: -20 //center
					},
					subtitle: {
						text: ' <?php echo $title["".$_GET["dirnamePrefix"]] ?>',
						x: -20
					},
					xAxis: {
						categories: [
						<?php
							for ($t = 0; $t <=3; $t++){
								for ($c = 1; $c<=19; $c++){
									echo "'".$t."_".$c."', ";
								}
							}
						?>],
						labels: {
							align: 'left',
							rotation: 90,
							step: 2
						},
						title: {
							enable: true,
							text: 'x_y',
						},
					},
					yAxis: {
						min: 0,
						title: {
							text: 'MRR'
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
							foreach ($rates as $methods => $row){
						?>
						{
						name: '<?php echo $methodsTitle[$methods]; ?>',
						data: [
							<?php 
								for ($t = 0; $t <=3; $t++){
									for ($c = 1; $c<=19; $c++){
										echo $row[$t."_".$c].", ";
									}
								}
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