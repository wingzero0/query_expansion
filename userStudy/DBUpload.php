<?php
require_once("/home/b95119/query_expansion/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);


$ret["result"] = false;

if ( isset($_POST["user"]) && isset($_POST["qPairID"]) && 
	isset($_POST["t"]) && isset($_POST["c"]) && isset($_POST["methodName"]) &&
	isset($_POST["nonRelevant"]) && isset($_POST["neutral"]) && isset($_POST["Relevant"]) && 
	isset($_POST["diversity"]) && isset($_POST["duplicate"]) &&
	isset($_POST["numberOfRecord"]) ){
		
	$sql = sprintf("select `id` from `UserStudyMethod` where `methodName` = '%s'", 
		$_POST["methodName"]);
	$result = mysql_query($sql);
	
	if (mysql_error()){
		$ret["err"] = $sql."(first q)\n".mysql_error();
		echo json_encode($ret);
		die();
	}else if ( $row = mysql_fetch_row($result) ){
		$methodID = $row[0];
	}else{ // no record
		$ret["err"] = "methodName errro\n".$sql."\n";
		echo json_encode($ret);
		die();
	}
	
	$sql = sprintf("
		insert into `UserStudyRecord` (`user`, `qPairID`, `t`, `c`, `methodID`,
			`nonRelevant`, `neutral`, `Relevant`,
			`diversity`, `duplicate`, `numberOfRecord`
		) values ('%s', %d, %d, %d, %d, %d, %d, %d, %d, %d, %d )",
		$_POST["user"],
		intval($_POST["qPairID"]),
		intval($_POST["t"]),
		intval($_POST["c"]),
		$methodID,
		intval($_POST["nonRelevant"]),
		intval($_POST["neutral"]),
		intval($_POST["Relevant"]),
		intval($_POST["diversity"]),
		intval($_POST["duplicate"]),
		intval($_POST["numberOfRecord"])
	);
	$result = mysql_query($sql); 
	if (mysql_error()){
		$ret["err"] = $sql."\n".mysql_error();
	}else{
		$ret["result"] = true;
	}
	echo json_encode($ret);
}else{
	$ret["err"] = "post attribute not enough\n `user`, `qPairID`, `t`, `c`, `methodName`, `resonable`, `diversity`, `duplicate`, `numberOfRecord`\n";
	echo json_encode($ret);
}


?>