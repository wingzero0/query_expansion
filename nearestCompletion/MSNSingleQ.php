<?php
// the program want to query google and get the recommendation of the original 
// then it will save to db

require_once(dirname(__FILE__)."/../connection.php");

mysql_select_db($database_cnn,$b95119_cnn);

class MSNSingleQ{
	public function WholeSQL($insertTb, $sourceTb, $filterTb){
		// I don't know why it doesn't work.
		$sql = sprintf(
			"insert into `%s` (`word`, `value`) values (
				select `Query`, `NumOfQuery` from `%s` where `Query` not in (
					select `word` from `%s` group by `word` 
				)
			)",
			$insertTb, $sourecTb, $filterTb
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
	}
	public function SQLbyCode($insertTb, $sourceTb, $filterTb){
		// tow nested select is too slow
		$sql = sprintf("select `word` from `%s` group by `word`",$filterTb);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		echo "getting fliter\n";
		while ($row = mysql_fetch_row($result) ){
			$q = addslashes($row[0]);
			$filter[$q] = true;
		}
		
		$sql = sprintf("select `Query`, `NumOfQuery` from `%s` group by `Query`",
			$sourceTb);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		echo "getting selectedQ\n";
		$selectedQ = array();
		while ($row = mysql_fetch_row($result) ){
			$q = addslashes($row[0]);
			if (!isset($filter[$q])){
				$selectedQ[$q] = $row[1];
			}
		}
		echo "insert Q\n";
		foreach ($selectedQ as $q => $v){
			$sql = sprintf(
				"insert into `%s` (`word`, `value`) values ('%s', %d)",
				$insertTb, $q, $v);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
	}
	public static function test(){
		$obj = new MSNSingleQ();
		$obj->SQLbyCode("Msn_SingleQ", "QueryCluster_5_Clean", "Aol_SingleQ");
	}
}

MSNSingleQ::test();

?>
