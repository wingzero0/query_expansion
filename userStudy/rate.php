<?php
require_once("/home/b95119/query_expansion/UserStudy.php");
$systemPath = "/home/b95119/query_expansion/";
$q1 = $_GET["q1"];
$q2 = $_GET["q2"];

$obj = new UserStudy($q1,$q2, $systemPath . "/snippyPath/", $systemPath . "/resultPath/", $systemPath . "/chuhancheng/tmpOutput/");
$snippy = $obj->GetQSnippy($q1);

$partialQ2 = array();
$completion = array();
for ($t = 0;$t<=1;$t++){
	for ($c = 1;$c <=3;$c++){
		$partialQ2[] = $obj->GeneratePartialQ2($t,$c);
		$completion[] = $obj->GetCompletion($_GET["methodName"], $t, $c);
		$settingT[] = $t;
		$settingC[] = $c;
	}
}

?>

<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>User Study</title>		
		<script src="jquery-1.7.2.min.js"></script>
		<script src="submit.js"></script>
		<link rel="stylesheet" href="rate.css" type="text/css" />	
	</head>
	<body>
		<div id="q1" style="float:left;width:400px">
<?php 
echo "<span class=\"h\">q1=".$q1."</span><br>";
echo "<span class=\"h\">We search \"" . $q1. "\" from Google:</span><br><div id=\"snippy\">".$snippy."</div><br>";
?>
		</div>
		<div id="space" style="float:left;width:50px">
		&nbsp;
		</div>
	<div id="q2" style="float:left;width:400px">
	<span class="h">q2=<?php echo $q2; ?></span>
<?php
foreach($partialQ2 as $i => $pq):
	$formID = "form".$i;
	if ( empty($pq) ):
		continue;
	endif;
?>
	<div id="<?php echo $formID; ?>" style="display:block">

	<span class="h">When entering "<span id="pq"><?php echo $pq; ?></span>"<br>
	The suggestion of q2:<br></span>
	<table>
	</table>

		<form name="rateValue" id="rateValue<?php echo $formID; ?>" action="">  
			<fieldset>  
			<table><th></th>
				<tr><td></td><td>Non Relevant</td><td>Neutral</td><td>Relevant</td></tr>
<?php
	foreach($completion[$i] as $j => $result){
		echo "<tr><td><span style='color:grey'>". ($j + 1) ." &nbsp </span>".$result."</td>";
		//$onclickStr = "addOne(document.getElementById('rateValue".$formID."'), 'resonable')";
		echo "<td><input type='radio' name='group".($j + 1).$formID."' value='nonRelevant' checked></td>";
		echo "<td><input type='radio' name='group".($j + 1).$formID."' value='neutral'></td>";
		echo "<td><input type='radio' name='group".($j + 1).$formID."' value='Relevant'></td></tr>";
	}
?>
				<!-- <tr>
				<td><label for="resonable" id="resonable_label">How many records are reasonable</label>  </td>
				<td><input type="text" name="resonable" id="resonable" size="2" value="0" class="text-input" /> </td>  
				<td><button type="button" name="add" onclick="addOne(this.form, 'resonable')">+1</button></td>
				</tr> --> 
				<tr>
				<td><label for="diversity" id="diversity_label">How many different and useful intents?</label> </td>  
				<td><input type="text" name="diversity" id="diversity" size="2" value="0" class="text-input" /> </td>
				<td><button type="button" name="add" onclick="addOne(this.form, 'diversity')">+1</button></td>
				</tr>  
				<tr>
				<td><label for="duplicate" id="duplicate_label">How many duplicate results?</label>  </td>
				<td><input type="text" name="duplicate" id="duplicate" size="2" value="0" class="text-input" /></td>
				<td><button type="button" name="add" onclick="addOne(this.form, 'duplicate')">+1</button></td>  
				</tr>
			</table>
			  
				<!-- <input type="hidden" name="fromID" value="<?php echo $formID; ?>"/> -->
				<input type="hidden" name="t" value="<?php echo $settingT[$i]; ?>"/>
				<input type="hidden" name="c" value="<?php echo $settingC[$i]; ?>"/>
				<input type="hidden" name="qPairID" value="<?php echo $_GET["qPairID"]; ?>"/>
				<input type="hidden" name="numberOfRecord" value="<?php echo count($completion[$i]); ?>"/>
				<input type="hidden" name="user" value="<?php echo $_GET['user']; ?>"/>
				<input type="hidden" name="methodName" value="<?php echo $_GET['methodName']; ?>"/>
				<button type="button" name="rateIt" onclick="rate(this.form, '<?php echo $formID; ?>');">Rate</button>
				<button type="button" name="skipIt" onclick="skip(this.form, '<?php echo $formID; ?>');">Skip</button>  
			</fieldset>  
		</form>  
	</div>
<?php 
endforeach;
?>
	<button type="button" name="back" onclick="window.location = './generateLink.php?user=<?php echo $_GET['user'];?>'">Back Page</button>
	
	</div>
	<!--<div style="clear:both"></div> -->
	</body>
</html>	
