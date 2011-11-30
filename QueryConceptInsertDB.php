<?php
// this program want to parse the data from query-cluster into db
require(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);


class QueryConceptInsertDB extends FileProcessUtility{
	public $c_fp;
	public $querys = array();
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
			$this->targetTB = "QueryCluster";
		}

	}
	public function ParseClusterNumberInFileName($filename){
		$pattern = "/(.*?)\.txt/";
		$ret = preg_match($pattern, $filename, $matches);
		if ($ret <= 0) {
			fprintf($this->err_fp, "filename not match formate:%s\n", $filename);
			return -1;
		}else {
			$this->clusterNum = intval($matches[1]); 
		}
		return 0;
	}
	public function ParseInput(){
		while (!feof($this->c_fp)){
			$line = fgets($this->c_fp);
			$line = $this->cut_last_newline($line);
			if (empty($line)){
				continue;
			}
			$this->querys[] = $this->convert_safe_str($line);
		}
		sort($this->querys);
	}
	public function run($query){
		echo $this->clusterNum."\n";
		$this->ParseInput();
		print_r($this->querys);
		$this->insertDB();
		/*
		$sql = sprintf(
			"select * from `srfp20060501-20060531` where `Query` = '%s'",
			$query
			);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		while($row = mysql_fetch_row($result)){
			print_r($row);
		}*/
	}
	public function insertDB(){
		$sql = sprintf(
			"CREATE TABLE if not exists `msn_click_log`.`%s` (
				`rowID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`Query` VARCHAR( 255 ) NOT NULL ,
				`ClusterNum` INT NOT NULL ,
				UNIQUE (  `Query` ,  `ClusterNum` )
			) ENGINE = MYISAM", $this->targetTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());

		foreach($this->querys as $i => $q){
			$sql = sprintf(
				"insert into `%s` (`Query`, `ClusterNum`) 
				values('%s', %d)", $this->targetTB, $q, $this->clusterNum);
			$result = mysql_query($sql) or die($sql."\n".mysql_error());
		}
	}
	private function __insertDB($q){
		$sql = sprintf(
			"insert into `%s` (`Query`, `ClusterNum`) 
			values('%s', %d)", $this->targetTB, $q, $this->clusterNum);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
	}
}

?>
