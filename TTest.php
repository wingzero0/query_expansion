<?php
// T-Test class want to test two words are frequently co-occur.
// if t value is large, they always appear together
// if t value is negative large, they won't appear together
// it t is about 0, they appear randomly

require(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class TTest{
	public $mTB;// two words co-occur freq
	public $sTB;// single word freq
	public $tTB;// save t-test value
	//public $sws;// stopwords
	public $N;
	public function __construct($matrixTB, $singleTB, $tTestTB, $N){
		$this->mTB = $matrixTB;
		$this->sTB = $singleTB;
		$this->tTB = $tTestTB;
		$this->N = $N;
	}
	public function TTestValue($xbar, $mu, $sVar){
		$t = ($xbar - $mu) * sqrt($this->N / $sVar);
		return $t;
	}
	public function GetStopWord(){
		$obj = new RemoveStopWord($this->tTB, $this->sTB);
		$sws = $obj->SelectStopWord();
		return $sws;
	}
	public function TTest($StopWords){
		// get single term
		$sql = sprintf(
			"select `word`, sum(`value`)
			from `msn_click_log`.`%s`
			group by `word`
			order by `word`",
			$this->sTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$singleTerm = array();
		while($row = mysql_fetch_row($result)){
			$word = addslashes($row[0]);
			$singleTerm[$word] = intval($row[1]);
		}
		//print_r($singleTerm);
		
		// get two word co-occur freq
		$sql = sprintf(
			"select `w1`, `w2`, `value`
			from `msn_click_log`.`%s`",
			$this->mTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());

		//$counter = 0;
		while($row = mysql_fetch_row($result)){
			$w1 = addslashes($row[0]);
			$w2 = addslashes($row[1]);
			$count12 = intval($row[2]);
			
			$flag = false;
			foreach ($StopWords as $w => $v){
				if (($w1 == $w) || ($w2 == $w)){
					//fprintf(STDERR, "get stop word:%s\n", $w);
					$flag = true;
					break;
				}
			}
			if ($flag == true){
				continue;
			}			
			
			if ( isset($singleTerm[$w1]) ){
				$count1 = $singleTerm[$w1];
			}else {
				fprintf(STDERR, "word:%s not found in sTB\n", $w1);
				continue;
			}
			
			if ( isset($singleTerm[$w2]) ){
				$count2 = $singleTerm[$w2];
			}else {
				fprintf(STDERR, "word:%s not found in sTB\n", $w2);
				continue;
			}
			
			$xbar = $count12 / $this->N;
			$mu = ($count1 / $this->N) * ($count2 / $this->N);
			$sVar = $xbar * (1-$xbar); 
			$t = $this->TTestValue($xbar, $mu, $sVar);
			
			$this->TValueInsert($w1,$w2,$t);
		}
		return;
	}
	public function CreateDB(){
		$sql = sprintf(
			"CREATE TABLE if not exists `msn_click_log`.`%s` (
				`id` INT NOT NULL AUTO_INCREMENT ,
				`Word1` VARCHAR( 255 ) NOT NULL ,
				`Word2` VARCHAR( 255 ) NOT NULL ,
				`TValue` DOUBLE NULL ,
				PRIMARY KEY (  `id` ) ,
				KEY `Word1` ( `Word1` ),
				KEY `Word2` ( `Word2` ),
				KEY  `TValue` (  `TValue` )
			) ENGINE = MYISAM DEFAULT CHARSET = utf8 COLLATE = utf8_bin", $this->tTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
	}
	public function TValueInsert($w1,$w2,$t){
		if (is_infinite($t) || is_nan($t) ){
			$sql = sprintf(
				"insert into `%s` (`Word1`, `Word2`, `TValue`) 
				values('%s', '%s', NULL)", $this->tTB,$w1,$w2);
			fprintf(STDERR, "w1:%s w2:%s is infinite or null:%lf", $w1, $w2, $t);	
		}else{
			$sql = sprintf(
				"insert into `%s` (`Word1`, `Word2`, `TValue`) 
				values('%s', '%s', %lf)", $this->tTB,$w1,$w2,$t);
		}
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
	}	
	public function Run(){
		$this->CreateDB();
		$sws = $this->GetStopWord();
		echo "StopWords:\n";
		print_r($sws);
		$this->TTest($sws);
	}
	public static function test() {
		$obj = new LLR("matrix_test", "word_test", "t_test", 200);
		$obj->Run();
	}
}

class RemoveStopWord{
	public $tTB;
	public $sTB;
	public function __construct($tTestTB, $singleTB){
		$this->tTB = $tTestTB;
		$this->sTB = $singleTB;
	}
	public function SelectStopWord(){
		$sql = sprintf(
			"select sum(`value`) from `%s`",
			$this->sTB
			);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		if ($row = mysql_fetch_row($result)){
			$sum = doubleval($row[0]);
		}else{
			fprintf(STDERR, "DB error\nsql:%s\n", $sql);
			return NULL;
		}
		
		$sql = sprintf(
			"select `word`, `value` from `%s` 
			order by `value` desc
			limit 0, 5
			",
			$this->sTB
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		
		$stopWords = array();
		while ($row = mysql_fetch_row($result)){
			$w = addslashes($row[0]);
			$freq = doubleval($row[1]);
			//$ratio = $freq / $sum;
			//echo $w." rate:".$ratio."\n";
			if ($freq / $sum < 0.01){
				break;
			} 
			$stopWords[$w] = intval($row[1]); // freq 
		}
		return $stopWords;
	}
	public function DeleteStopWord($stopWords){
		foreach ($stopWords as $w => $freq){
			$sql = sprintf(
				"DELETE FROM `msn_click_log`.`%s` 
				WHERE Word1 = '%s' or Word2 = '%s'",
				$this->tTB, $w, $w
			);
			//echo $sql."\n";
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
	}
	
	public function test(){
		$obj = new RemoveStopWord("t_test_4", "word_test_4");
		$sws = $obj->SelectStopWord();
		$obj->DeleteStopWord($sws);
		//print_r($sw);
	}
}

//RemoveStopWord::test();
?>
