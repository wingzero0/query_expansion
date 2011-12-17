<?php
// LLR memory version, it will get all term in the memory.

// Class LLR want to calculate the log likelihood ratios of the words in msn log
// It assumes it can read a matrix and a table in the DB.
// The value of entry (i,j) of the matrix represents the times of word i and word j 
// appear together.
// The value in the table represents the times which a word appeared. 

// Class SingleTermCountToDB read the input file and dump the words and their frequence to DB
// The file format is shown as follow
// the first line is the total word frequence.
// the remaining lines are written in the same format. 
// It has a number at the begining followed with tab. 
// The word is written at the end of the line.

require(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class LLR{
	public $mTB;
	public $sTB;
	public $llrTB;
	public $N;
	public $probTable;
	public $coeffTable;
	public function __construct($matrixTB, $singleTB, $llrTB, $N){
		$this->mTB = $matrixTB;
		$this->sTB = $singleTB;
		$this->llrTB = $llrTB;
		$this->N = $N;
	}
	public function LogPow($p,$k){
		$res = $k * log10($p);		
		return $res;
	}
	public function LogBinomialDistribution($n,$k, $p){
		if ($p == 0.0){
			//echo "$p = 0.0\n";
			return log($p); // let it get INF 
		}
		//$p1 = pow($p,$k);
		//$p2 = pow(1-$p,$n - $k);
		//$prob = log10($p1) + log10($p2);
		$p1 = $this->LogPow($p,$k);
		$p2 = $this->LogPow( 1 - $p, $n - $k);
		$prob = $p1 + $p2;		
		return $prob;
	}
	public function GetCount(){
		
		$sql = sprintf(
			"select `word`, `value`
			from `msn_click_log`.`%s`",
			$this->sTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$singleTerm = array();
		while($row = mysql_fetch_row($result)){
			$word = addslashes($row[0]);
			$singleTerm[$word] = intval($row[1]);
		}
			
			
		$sql = sprintf(
			"select `w1`, `w2`, `value`
			from `msn_click_log`.`%s`",
			$this->mTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$llrArray = array();
		$counter = 0;
		while($row = mysql_fetch_row($result)){
			//if ($counter % 1000 == 0){
				//echo $counter."\n";
			//}
			//$w1 = $row[0];
			//$w2 = $row[1];
			$w1 = addslashes($row[0]);
			$w2 = addslashes($row[1]);
			$count12 = $row[2];
			
			if ( isset($singleTerm[$w1]) ){
				$count1 = $singleTerm[$w1];
			}else {
				fprintf(STDERR, "word:%s not found in sTB", $w1);
				continue;
			}
			
			if ( isset($singleTerm[$w2]) ){
				$count2 = $singleTerm[$w2];
			}else {
				fprintf(STDERR, "word:%s not found in sTB", $w2);
				continue;
			}
			
			//fprintf(STDERR, "w1:%s\tw2:%s\n", $w1, $w2);
			$llr = $this->CalculateLLR($count12, $count1, $count2, $this->N);
			
			$this->LLRSingleInsert($w1,$w2,$llr);
			
			//$llrArray[$w1][$w2] = $llr;
			//$counter++;
		}
		return $llrArray;
	}
	public function CalculateLLR($count12, $count1, $count2, $N) {
		// Hypothesis 1, a formalization of independent;
		// H1: p(w2 | w1) = p = p(w2 | not w1) = c2 / N;
		// L(H1) = b(c1;c12, p) * b(n-c1;c2-c12, p)
		// Hypothesis 2, a formalization of independent;
		// H2: p(w2 | w1) != p(w2 | not w1); p1 = c12/c1; p2 = (c2-c12)/(N-c1)
		// L(H2) = b(c1;c12, p1) * b(n-c1;c2-c12, p2)
		
		//fprintf(STDERR, "\tc12:%d\tc1:%d\tc2:%d\tN:%d\n", $count12,$count1,$count2,$N);
		$p = $count2 / $N;
		$H11 = $this->LogBinomialDistribution($count1, $count12, $p);
		$H12 = $this->LogBinomialDistribution($N - $count1, $count2 - $count12, $p);
		//fprintf(STDERR, "\tH11:%lf\tH12:%lf\n", $H11,$H12);
		
		$logH1 = $H11 + $H12;
			
		$p1 = $count12 / $count1;
		$p2 = ($count2-$count12)/($N-$count1);
		
		$H21 = $this->LogBinomialDistribution($count1, $count12, $p1);
		$H22 = $this->LogBinomialDistribution($N - $count1, $count2 - $count12, $p2);
		//fprintf(STDERR, "\tH21:%lf\tH22:%lf\n", $H21,$H22);

		$logH2 = $H21 + $H22;
		
		//fprintf(STDERR, "\tlogH1:%lf\tlogH2:%lf\n", $logH1,$logH2);
		$logLLR = $logH1 - $logH2;
		return $logLLR; 
	}
	public function CreateDB(){
		$sql = sprintf(
			"CREATE TABLE if not exists `msn_click_log`.`%s` (
				`id` INT NOT NULL AUTO_INCREMENT ,
				`Word1` VARCHAR( 255 ) NOT NULL ,
				`Word2` VARCHAR( 255 ) NOT NULL ,
				`LLR` DOUBLE NULL ,
				PRIMARY KEY (  `id` ) ,
				KEY `Word1` ( `Word1` ),
				KEY `Word2` ( `Word2` ),
				KEY  `LLR` (  `LLR` )
			) ENGINE = MYISAM DEFAULT CHARSET = utf8 COLLATE = utf8_bin", $this->llrTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
	}
	public function LLRSingleInsert($w1,$w2,$llr){
		if (is_infinite($llr) || is_nan($llr) ){
			$sql = sprintf(
				"insert into `%s` (`Word1`, `Word2`, `LLR`) 
				values('%s', '%s', NULL)", $this->llrTB,$w1,$w2);
		}else{
			$sql = sprintf(
				"insert into `%s` (`Word1`, `Word2`, `LLR`) 
				values('%s', '%s', %lf)", $this->llrTB,$w1,$w2,$llr);
		}
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
	}	
	public function Run(){
		$this->CreateDB();
		$llrArray = $this->GetCount();
		//$this->LLRInsert($llrArray);
	}
	public static function test() {
		$obj = new LLR("matrix_test", "word_test", "llr_test", 200);
		$obj->Run();
	}
}
//LLR::test();

class SingleTermCountToDB{
	public $fp;
	public $tb;
	public $N;
	public function __construct($inFile, $tbName){
		$this->fp = fopen($inFile, "r");
		if ($this->fp == null){
			fprintf(STDERR, $inFile . " can't be opened\n");
			exit(-1);
		}
		$this->tb = $tbName;
	}
	public function __destruct(){
		fclose($this->fp);
	}
	public function InsertDB(){
		fscanf($this->fp, "%d", $this->N);
		$ret = fscanf($this->fp, "%d %s", $freq, $word);
		while ($ret != 0){
			//echo $word."--".$freq."\n";
			$sql = sprintf(
				"insert into `%s` (`word`, `value`) values
				('%s', %d)",
				$this->tb, addslashes($word), $freq
			);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
			$ret = fscanf($this->fp, "%d %s", $freq, $word);
		}
	}
	public function test(){
		$obj = new SingleTermCountToDB("/home/chuhancheng/project_ir/allterm.txt", "word_test");
		$obj->InsertDB();
	}
}

//SingleTermCountToDB::test();
?>
