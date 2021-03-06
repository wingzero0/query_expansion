<?php
// Class WordConceptInsertDB want to parse the data from query-cluster into db
// the query-cluster file was generated by 上祺
// the cluster id is specified in the file name.
// each line in the file is the query belonged to the cluster.

// class WordConceptClean do the similar thing. 
// But it read QueryConceptClean as input and 
// split the query and count the word freq directly.

require(dirname(__FILE__)."/QueryConceptInsertDB.php");
//mysql_select_db($database_cnn,$b95119_cnn);


class WordConceptInsertDB extends QueryConceptInsertDB{
	public $c_fp;
	public $words = array();
	public $clusterNum;
	public $targetTB;
	public function __construct($para){
		parent::__construct($para);
		if (isset($para["c"])){
			$this->c_fp = fopen($para["c"], "r");
			if ($this->c_fp == NULL){
				fprintf($this->err_fp, "%s can't be opened\n", $para["c"]);
				exit(-1);
			}
		}else{
			fprintf($this->err_fp, "please specify the cluster (or concept) file with option \"-c\"\n");
			exit(-1);
		}
		$ret = $this->ParseClusterNumberInFileName($para["c"]);
		if ($ret == -1){
			exit(-1);
		}
		if (isset($para["TB"])){
			$this->targetTB = $para["TB"];
		}else {
			$this->targetTB = "WordCluster";
		}

	}
	public function ParseInput(){
		$pattern = "\s";
		while (!feof($this->c_fp)){
			$line = fgets($this->c_fp);
			if (empty($line)){
				continue;
			}
			$line = $this->convert_safe_str($line);
			$list = mb_split($pattern, $line);
			if (empty($list)){
				continue;
			}
			foreach( $list as $word ){
				if (!empty($word)){
					$tmp[$word] = 1;// set and reset
				}
			}
		}
		ksort($tmp);
		$this->words = array_keys($tmp);
	}
	public function run(){
		echo $this->clusterNum."\n";
		$this->ParseInput();
		$this->insertDB();
	}
	public function insertDB(){
		$sql = sprintf(
			"CREATE TABLE if not exists `msn_click_log`.`%s` (
				`rowID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`Word` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
				`ClusterNum` INT NOT NULL ,
				`NumOfWord` INT NULL COMMENT  'records the num of target word appeared in cluster',
				UNIQUE (  `Word` ,  `ClusterNum` ),
		INDEX (  `NumOfWord` )
	) ENGINE = MYISAM", $this->targetTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		
		foreach($this->words as $i => $w){
			$sql = sprintf(
				"insert into `%s` (`Word`, `ClusterNum`) 
				values('%s', %d)", $this->targetTB, $w, $this->clusterNum);
			//echo $sql."\n";
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
	}
}

class WordConceptNumOfQuery extends FileProcessUtility{
	// non-complete
	// the query read from Session file should be splited to words
	// thu Update SQL column should change
	public $fp;
	public $words;
	public $targetTB;
	public function __construct($para){
		//mb_internal_encoding("UTF-8");
		parent::__construct($para);
		if (isset($para["s"])){
			$this->fp = fopen($para["s"], "r");
			if ($this->fp == NULL){
				fprintf($this->err_fp, "%s can't be opened\n", $para["s"]);
				exit(-1);
			}
		}else{
			fprintf($this->err_fp, "please specify the session query data file with option \"-s\"\n");
			exit(-1);
		}
		if (isset($para["TB"])){
			$this->targetTB = $para["TB"];
		}else {
			$this->targetTB = "WordCluster";
		}
	}
	public function ParseSession(){
		$this->words = array();
		while (!feof($this->fp)){
			$line = fgets($this->fp);
			$line = $this->cut_last_newline($line);
			if (empty($line)){
				continue;
			}
			//$list = mb_split("\t", $line);
			$list = split("\t", $line);
			if (count($list) < 5){
				echo "list count < 5. line format error:$line\n"; 
				continue;
			}
			$num = intval($list[0]);
			for ($i= 0;$i<$num;$i++){
				$index = $i *3 + 4;
				if ( !isset($list[$index])){
					echo "index not set $index\nline format error:$line\n";
					continue;
				}
				$q = addslashes($list[$index]);
				if (!isset($this->words[$q])){
					$this->words[$q] = 0;
				}
				$this->words[$q] += 1;
			}
		}
		fclose($this->fp);
	}
	public function UpdateDB(){
		echo "total query:".count($this->words)."\n";
		$counter = 0;
		foreach ($this->words as $q => $v){
			//if ($counter % 10000 == 0){
				//echo $counter;
			//}
			$sql = sprintf(
				"update `%s` set `NumOfQuery` = %d where `query` = '%s'",
				$this->targetTB, $v, $q
			);
			//echo $sql."\n";
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
			//$counter++;
		}
	}
	public function Run(){
		$this->ParseSession();
		$this->UpdateDB();
		//echo "update end\n";
	}
}

class WordConceptClean {
	public $qTB;
	public $wTB;
	public $clusterQ;
	public $clusterW;
	public function __construct($qConceptTB, $wConceptTB){
		$this->qTB = $qConceptTB;
		$this->wTB = $wConceptTB;
	}
	public function LoadQuery(){
		$sql = sprintf(
			"select `Query`, `ClusterNum`, `NumOfQuery`
			from `%s`
			",
			$this->qTB
		);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		while ($row = mysql_fetch_assoc($result)){
			$q = addslashes($row["Query"]);
			$c = intval($row["ClusterNum"]);
			$clicked = intval($row["NumOfQuery"]);
			$this->clusterQ[$c][$q] = $clicked; 
		}
	}
	public function SplitWord(){
		foreach($this->clusterQ as $c => $qs){
			foreach ($qs as $q => $v){
				$words = mb_split(" ", $q);
				foreach ($words as $w){
					if ( !isset($this->clusterW[$c][$w]) ){
						$this->clusterW[$c][$w] = 0;
					}
					$this->clusterW[$c][$w] += $v;
				}
			}
		}	
	}
	public function SaveWord(){
		$sql = sprintf(
			"CREATE TABLE if not exists `msn_click_log`.`%s` (
				`rowID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`Word` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
				`ClusterNum` INT NOT NULL ,
				`NumOfWord` INT NULL COMMENT  'records the num of target word appeared in cluster',
				UNIQUE (  `Word` ,  `ClusterNum` ),
				INDEX (  `NumOfWord` )
			) ENGINE = MYISAM", $this->wTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		foreach ($this->clusterW as $c => $ws){
			foreach ($ws as $w => $v){
				$sql = sprintf(
					"insert into `%s` (`Word`, `ClusterNum`, `NumOfWord`) 
					values('%s', %d, %d)", 
					$this->wTB, $w, $c, $v
				);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
	public function run(){
		$this->LoadQuery();
		$this->SplitWord();
		$this->SaveWord();	
	}
}
?>
