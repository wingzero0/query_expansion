<?php
// Class CoClickSuggestion wants to suggestion querys to a input query. 
// if the queries have been clicked with a same url with the input query, they are the suitable query
// with entropy
// the queries are ranked by click probability. p(q2 | q1) = sum (u) p(q1->u->q2) 

require_once(dirname(__FILE__)."/../connection.php");

mysql_select_db($database_cnn,$b95119_cnn);

class CoClickQuerySuggestion{
	//public $vectorTB;
	protected $queryClick; // [$q][$u] = $v
	protected $queryProb; // [$q][$u] = $p
	protected $urlClick; // [$u][$q] = $v
	protected $urlProb; // [$u][$q] = $p
	public $q2qProb;
	public function __construct(){
	}
	public function InitFromFile($filename){
		$fp = fopen($filename, "r");
		if ($fp == NULL){
			fprintf(STDERR, "%s can't be read\n",$filename);
			return -1;
		}
		// read file
		fprintf(STDERR, "reading file:%s\n",$filename);
		$line = fgets($fp); // drop the first line
		$tmp = array();
		//$counter = 0;
		while ($line = fgets($fp)){
			//if ($counter % 10 ==0){
				//echo $counter."\n";
			//}
			//$counter++;
			$line = trim($line);
			if (empty($line)){
				continue;
			}
			$list = preg_split("/\t/", $line);
			if (count($list) != 5 && count($list) != 4){
				fprintf(STDERR, "parse error, count = %d:\n%s\n",count($list),$line);
				continue;
			}
			$q = trim(addslashes($list[1]));
			$u = addslashes($list[3]);
			if (!isset($tmp[$q])){
				$sum[$q] = 0;
			}
			if (!isset($tmp[$q][$u])) {
				$tmp[$q][$u] = 0;
			}
				
			$tmp[$q][$u] += 1;
			$sum[$q] += 1;
		}
		fclose($fp);
		
		// drop low freq
		foreach ($tmp as $q => $uArray){
			if ($sum[$q] > 5){ // skip the low freq part
				foreach ($uArray as $u => $click){
					$this->queryClick[$q][$u] = $click;
					$this->urlClick[$u][$q] = $click;
				}
			}
		}
		
		fprintf(STDERR, "partial prob\n");
		// calc partial prob
		foreach ($this->queryClick as $q => $uArray){
			foreach ($uArray as $u => $click){
				$this->queryProb[$q][$u] = (double) $click / (double) $sum[$q];
			}
		}
		
		foreach ($this->urlClick as $u => $qArray){
			$urlSum = 0;
			foreach ($qArray as $q => $click){
				$urlSum += $click;
			}
			foreach ($qArray as $q => $click){
				$this->urlProb[$u][$q] = (double) $click / (double) $urlSum;
			}
		}
		//echo "queryProb\n";
		//print_r($this->queryProb);
		//echo "urlProb\n";
		//print_r($this->urlProb);
		
		// calculate prob
		$this->CalculateQ2Q();
	}
	protected function CalculateQ2Q(){
		$this->q2qProb = array(); // clear
		foreach ($this->queryProb as $q1 => $uArray){
			foreach ($uArray as $u => $q2uProb){
				if ( !isset($this->urlProb[$u]) ){
					continue;
				}
				foreach ($this->urlProb[$u] as $q2 => $u2qProb){ // summation of different url
					if ( !isset($this->q2qProb[$q1][$q2]) ){
						$this->q2qProb[$q1][$q2] = 0.0;
					}
					$this->q2qProb[$q1][$q2] += $q2uProb * $u2qProb;
				}
				arsort($this->q2qProb[$q1]);
			}
		}
		//return $this->q2qProb;
	}
	public function SaveQ2QDB($tableName){
		foreach ($this->q2qProb as $q1 => $q2Array){
			foreach ($q2Array as $q2 => $prob){
				$sql = sprintf(
					"insert into `%s` (`q1`, `q2`, `prob`) values ('%s', '%s', '%lf')",
					$tableName, $q1, $q2, $prob
				);
				$result = mysql_query($sql) or die($sql."\n".mysql_error());
			}
		}
	}
	public function InitFromDB($Q2QTableName){
		$sql = sprintf(
			"select `q1`, `q2`, `prob` from `%s` 
			order by `q1` asc, `prob` desc", $Q2QTableName);
		$result = mysql_query($sql) or die($sql."\n".mysql_error());
		while($row = mysql_fetch_row($result)){
			$q1 = addslashes($row[0]);
			$q2 = addslashes($row[1]);
			$this->q2qProb[$q1][$q2] = doubleval($row[2]);
		}
		return $this->q2qProb;
	}
	public function GetSuggestion($safeQ, $limit = -1){		
		if ( !isset($this->q2qProb[$safeQ]) ){
			return array(); // empty array;
		} else if ($limit == -1){
			return array_keys($this->q2qProb[$safeQ]);
		}
		$tmp = array_keys($this->q2qProb[$safeQ]);
		if ($limit < count($tmp)){
			$num = $limit;
		}else{
			$num = count($tmp);
		}
		$ret = array();
		for($i =0;$i < $num; $i++){
			$ret[$i] = $q; // copy
		}
		return $ret;
	}
}
//NearestCompletion::test();
?>
