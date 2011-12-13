<?php
// Class ConceptFlow want to calculate the probability of the Concept flow event.
// It just read the db created by ChuHan  

require(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class ConceptFlow extends FileProcessUtility{
	public $oldTB;
	public $newTB;
	public function __construct($para){
		parent::__construct($para);
		if (isset($para["oldTB"])){
			$this->oTB = $para["oldTB"];
		}else {
			$this->oTB = "cluster_pair";
		}
		if (isset($para["newTB"])){
			$this->nTB = $para["newTB"];
		}else {
			$this->nTB = "ClusterFlowProb";
		}
	}
	public function run(){
		$prob = $this->CalculateProb();
		$this->insertDB($prob);
	}
	public function CalculateProb(){
		$sql = sprintf(
			"select `cluster1`, `cluster2`, `pair_value` 
			from `msn_click_log`.`%s`
			order by `cluster1`, `cluster2`",
			$this->oTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$sum = 0;
		$allFlow =array();
		while($row = mysql_fetch_row($result)){
			$allFlow[$row[0]][$row[1]] = $row[2];
		}
		$sum = array();
		$prob = array();
		foreach($allFlow as $c1 => $v){
			$sum[$c1] = 0.0;
			foreach($v as $c2 => $count_v){
				$sum[$c1] += $count_v; 
			}
			foreach($v as $c2 => $count_v){
				$prob[$c1][$c2] = (double)$count_v / (double) $sum[$c1]; 
			}
		}
		return $prob;
	}
	public function insertDB($flowProb){
		$sql = sprintf(
			"CREATE TABLE if not exists `msn_click_log`.`%s` (
				`Cluster1` INT( 11 ) NOT NULL ,
				`Cluster2` INT( 11 ) NOT NULL ,
				`Prob` DOUBLE NOT NULL ,
				PRIMARY KEY (  `Cluster1` ,  `Cluster2` ) ,
				KEY  `Prob` (  `Prob` )
			) ENGINE = MYISAM", $this->nTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());

		foreach($flowProb as $c1 => $v){
			foreach($v as $c2 => $prob){
				$sql = sprintf(
					"insert into `%s` (`Cluster1`, `Cluster2`, `Prob`) 
					values(%d, %d, %lf)", $this->nTB,$c1,$c2,$prob);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
}

?>
