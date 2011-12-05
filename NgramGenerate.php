<?php
// Class NgramGenerate wants to generate the query into n-gram.
// CombinationNgramGenerate just generate the n-grab by terms possible combination.

//require_once(dirname(__FILE__)."/QuerySpliter.php");

class NgramGenerate{
	public $qWords;
	public $q;
	//private $querySpliter;
	public function __construct($query){
		// the query should be already splite by white and save into an array 
		mb_internal_encoding("UTF-8");
		$this->q = NULL;
		$this->qWords = NULL;
		$this->ReplaceNewQuery($query);
	}
	public function ReplaceNewQuery($query){
		if ($query != $this->q){
			$this->q = $query;
			$this->qWords = null;
			return 0;
		}else{
			return 1;
		}
	}
	public function GetQWords(){
		if ($this->qWords == NULL){ 
			$this->qWords = $this->SplitWord();
		}
		return $this->qWords;
	}
	public function GetNgrams($n){
		if ($n <1){
			return NULL;
		}

		$Ngrams = array();

		if ($n == 1){
			$pattern = "\s";
			$list = mb_split($pattern, $this->q);
			foreach ($list as $i => $v){
				if (empty($v)){
					continue;
				}
				$Ngrams[] = $v;
			}
			return $Ngrams;
		}

		if ($this->qWords == NULL){ 
			$this->qWords = $this->SplitWord();
		}

		$start = 0;
		$end = $n - 1;
		
		while($end < count($this->qWords)) {
			//concate the words
			$tmp = "";
			for ($i = $start;$i<=$end;$i++){
				$tmp.= $this->qWords[$i];
			}
			$Ngrams[] = $tmp;
			$start++;
			$end++;
		}
		return $Ngrams;
	}
	private function SplitWord(){
		// split the query by white space
		// the white space still attached to the previous term 
		$tmpQ = $this->q;
		//$pattern = "/(.*?)\s/";
		$pattern = "(.*?)\s";
		//$ret = preg_match($pattern, $tmpQ, $matches);
		$ret = mb_ereg($pattern, $tmpQ, $matches);
		//print_r($matches);
		$segment = array();
		while($ret != 0){
			$segment[] = $matches[0];
			//$list = preg_split("/\s/", $tmpQ, 2);
			$list = mb_split("\s", $tmpQ, 2);
			if (count($list) < 2){
				break;
			}
			$tmpQ = $list[1];
			//$ret = preg_match($pattern, $tmpQ, $matches);
			$ret = mb_ereg($pattern, $tmpQ, $matches);
		}
		if (!empty($tmpQ)){
			$segment[] = $tmpQ;
		}
		return $segment;
	}
	public static function test(){
		$obj = new NgramGenerate("good morning\tev");
		$obj->ReplaceNewQuery("ha中\tmorning\tev\naa");
		$ret = $obj->GetNgrams(2);
		foreach($ret as $i => $w){
			echo "'".$w."'\n";
		}
	}
	public static function SimpleUse($query){
		$obj = new NgramGenerate($query);
		$ret = $obj->SplitTerm();
		return $ret;
	}
}
//NgramGenerate::test();
?>
