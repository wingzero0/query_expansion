<?php
function LoadValidNumber($filename){
	$fp = fopen($filename, "r");
	if ($fp == null){
		echo $filename." can't be open<br>";
		return array();
	}
	$rates = array();
	while ($line = fgets($fp)){
		$list = preg_split("/\t/", $line);
		if ( count($list) != 3) {
			echo "formate error:".$line."\n";
			continue;
		}
		$index = $list[1];
		$value = intval($list[2]);
		$num[ $index ] = $value;
	}
	//print_r($num);
	fclose($fp);
	return $num;
}

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
	$methods = array(
		"completionPure", 
		"baseline", 
		"pairandfreq", 
		"flowandfreq", 
		"nearestPure", "completionEntropy", "nearestHybrid");
	
	$rates = array();
	foreach ($methods as $i => $name){
		$filename = $dirname."/".$name."_all.txt";
		$rates[$name] = GetFileRecords($filename);
	}
	return $rates;
}

$nums = LoadValidNumber($_GET["dirnamePrefix"]."DataStatictics.txt");

$methodsTitle = array("completionPure" => "ConceptCompletion", 
	"baseline" => "Pure Frequency", 
	"pairandfreq" => "Pair Frequency", 
	"flowandfreq" => "Concept Frequnecy", 
	"nearestPure" => "Nearest",
	"completionEntropy" => "ConceptCompletion + entropy",
	"nearestHybrid" => "NearestHybrid"
);
	
$totalSum = 0;
for($t = 0;$t < 4;$t++){
	$sumT[$t] = 0;
	for ($i = 1;$i<=10;$i++){
		foreach ($methodsTitle as $method => $title){
			$avg[$t][$method][$i] = 0.0;
			$allAvg[$method][$i] = 0.0; //duplicate init
		}
	}
	for ($c = 1;$c < 20;$c++){
		$index = $t."_".$c;
		$sumT[$t] += $nums[$index];
		$totalSum += $nums[$index];
	}
}

//echo $totalSum."<br>";
for($t = 0;$t < 4;$t++){
	for ($c = 1;$c < 20;$c++){
		$index = $t."_".$c;		
		$dirname = $_GET["dirnamePrefix"].$index;
		$rates = GetRecords($dirname);
		foreach ($methodsTitle as $method => $title){
			for($i = 1; $i <=10; $i++){
				$avg[$t][$method][$i] += $nums[$index] * $rates[$method][$i];
				$allAvg[$method][$i] += $nums[$index] * $rates[$method][$i];
			}
		}
	}
}

foreach ($methodsTitle as $method => $title){
	for($i = 1; $i <=10; $i++){
		for ($t = 0;$t < 4;$t++){
			$avg[$t][$method][$i] /= ( double )$sumT[$t];
		}
		$allAvg[$method][$i] /= (double)$totalSum;
	}
}

$title = array(
	"ttest_" => "Dependent data", 
	"independent" => "Independent Data",
	"" => "All Data"
);
//print_r($avg);
//print_r($allAvg);
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
						width: 800, //original 1000
						marginRight: 300,
						marginBottom: 50
					},
					title: {
						text: ' Average Inclusion Rate ',
						x: -20 //center
					},
					subtitle: {
						text: ' <?php echo $title[$_GET["dirnamePrefix"]];?>',
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
							text: 'Average Inclusion Rate'
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
						itemWidth: 300,
						//x: 50,
						y: 100,
						borderWidth: 0
					},
					series: [
						<?php
							if ($_GET["display"] == "all"){
								for ($t = 0;$t < 4;$t++){
									foreach( $methodsTitle as $method => $title ){
						?>
						{
						name: '<?php echo $methodsTitle[$method]." t=".$t ?>',
						data: [
									<?php 
										for ($i = 1;$i<= 10;$i++){
											echo $avg[$t][$method][$i].",";
										}
									?>]
						},
						<?php
									}
								}
							}else if ( isset($_GET["display"]) && is_numeric($_GET["display"])){
								$t = $_GET["display"];
								foreach( $methodsTitle as $method => $title ){
						?>
						{
						name: '<?php echo $methodsTitle[$method]." x=".$t ?>',
						data: [
								<?php 
									for ($i = 1;$i<= 10;$i++){
										echo $avg[$t][$method][$i].",";
									}
								?>]
						},
						<?php
								}
							}else{
								foreach( $methodsTitle as $method => $title ){
						?>
						{
						name: '<?php echo $methodsTitle[$method] ?>',
						data: [
								<?php 
									for ($i = 1;$i<= 10;$i++){
										echo $allAvg[$method][$i].",";
									}
								?>]
						},
						<?php
								}
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
