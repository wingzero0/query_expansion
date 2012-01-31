<?php
// Class PairProb want to calculate p(q2 | q1)
// It just read aol_pair_nqq

require(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class PairProb{
	public $oTB;
	public $nTB;
	public function __construct($oldTB, $newTB){
		$this->oTB = $oldTB;
		$this->nTB = $newTB;
	}
	public function run(){
		$prob = $this->CalculateProb();
		$this->insertDB($prob);
	}
	public function CalculateProb(){
		$sql = sprintf(
			"select `w1`, `w2`, `value` 
			from `msn_click_log`.`%s`
			order by `w1`, `w2`",
			$this->oTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		$allFlow =array();
		while($row = mysql_fetch_row($result)){
			$allFlow[$row[0]][$row[1]] = $row[2];
		}
		$sum = array();
		$prob = array();
		foreach($allFlow as $q1 => $v){
			$sum[$q1] = 0.0;
			foreach($v as $q2 => $count_v){
				$sum[$q1] += $count_v; 
			}
			foreach($v as $q2 => $count_v){
				$prob[$q1][$q2] = (double)$count_v / (double) $sum[$q1]; 
			}
		}
		return $prob;
	}
	public function insertDB($flowProb){
		$sql = sprintf(
			"CREATE TABLE if not exists `msn_click_log`.`%s` (
				`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
				`w1` VARCHAR( 255 ) COLLATE utf8_bin NOT NULL ,
				`w2` VARCHAR( 255 ) COLLATE utf8_bin NOT NULL ,
				`prob` double NOT NULL ,
				PRIMARY KEY (  `id` ) ,
				KEY  `w1` (  `w1` ) ,
				KEY  `w2` (  `w2` ) ,
				KEY  `prob` (  `prob` )
			) ENGINE = MYISAM DEFAULT CHARSET = utf8 COLLATE = utf8_bin",
			$this->nTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());

		foreach($flowProb as $q1 => $v){
			foreach($v as $q2 => $prob){
				$sql = sprintf(
					"insert into `%s` (`w1`, `w2`, `prob`) 
					values('%s', '%s', %lf)", $this->nTB,$q1,$q2,$prob);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
	public static function test(){
		$obj = new PairProb("Aol_pair_nqq","Aol_pair_prob");
		$obj->run();
	}
}
PairProb::test();
?>
