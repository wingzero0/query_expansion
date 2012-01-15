<?php
// Class NStepFlow want to calulate the N step prob, which is initially generated by ConceptFlow table

require(dirname(__FILE__)."/connection.php");
mysql_select_db($database_cnn,$b95119_cnn);

class NStepFlow{
	public $oTB;
	public $nTB;
	public $presentFlow;
	protected $nextFlow;
	public $n; // n step
	public function __construct($oldTB, $newTB, $nStep){
		$this->oTB = $oldTB;
		$this->nTB = $newTB;
		$this->n = $nStep;
	}
	public function InitProb(){
		//echo $this->oTB."\n";
		$sql = sprintf(
			"select `cluster1`, `cluster2`, `Prob` 
			from `msn_click_log`.`%s`
			order by `cluster1`, `cluster2`",
			$this->oTB);
		$result = mysql_query($sql) or die($sql."\n".mysql_error()."\n");
		$sum = 0;
		$this->presentFlow =array();
		while($row = mysql_fetch_row($result)){
			$this->presentFlow[$row[0]][$row[1]] = doubleval($row[2]);
		}
	}
	public function GoOneStep(){
		$this->nextFlow = array(); // clear memory
		foreach ($this->presentFlow as $i => $v1){ // $v1 is an array
			foreach ($v1 as $j => $v2){ // $v2 is first step prob
				if (isset($this->presentFlow[$j])){
					foreach ($this->presentFlow[$j] as $k => $v3){ // $v3 is the second step prob
						if ( !isset($this->nextFlow[$i][$k]) ){
							if ( !isset($this->presentFlow[$i][$k]) ){
								$this->nextFlow[$i][$k] = 0.0;
							}else{
								$this->nextFlow[$i][$k] = $this->presentFlow[$i][$k]; // init the prob with one step
							}
						}
						$this->nextFlow[$i][$k] += $v2 * $v3;
					}
				}
			}
		}
		$this->presentFlow = $this->nextFlow;
	}
	public function SaveProb($flowProb){
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
	public static function OneStep(){
		$obj = new NStepFlow("ClusterFlowProb_4","ClusterFlowProb_4_2",1);
		$obj->InitProb();
		$obj->GoOneStep();
		$obj->SaveProb($obj->presentFlow);
	}
}

NStepFlow::OneStep();

?>