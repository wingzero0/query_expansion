<?php
// Class QuerySpliter wants to split the query into multiple terms by white space.
// Two kinds of term will be return.
// One is completed term, other one is non-completed.
// One kind of terms may be Null, but all kinds are not NULL in the same time.  

class QuerySpliter{
	public $targetTB;
	public $q;
	public $qSegment;
	public function __construct($q){
		mb_internal_encoding("UTF-8");
		$this->ReplaceNewQuery($q); 
	}
	public function GetQWords(){
		return $this->qSegment;
	}
	public function GetNgrams($n){
		if ($n <1){
			return NULL;
		}

		$Ngrams = array();

		if ($n == 1){
			return $this->qSegment;
		}

		$start = 0;
		$end = $n - 1;
		
		while($end < count($this->qSegment)) {
			//concate the words
			$tmp = $this->qSegment[$start];
			for ($i = $start + 1;$i<=$end;$i++){
				$tmp.= " ".$this->qSegment[$i];
			}
			$Ngrams[] = $tmp;
			$start++;
			$end++;
		}
		return $Ngrams;
	}
	public function ReplaceNewQuery($q){
		$this->q = $q;
		$this->qSegment = $this->_SplitTerm();
	}
	private function _SplitTerm(){
		$pattern = "\s";
		//$list = preg_split($pattern, $this->q);
		$list = mb_split($pattern, $this->q);
		return $list;
	}
	public function SplitTerm(){
		$list = $this->_SplitTerm();
		$ret["word"] = array();
		$ret["partial"] = NULL;
		for ($i = 0;$i < count($list) - 1; $i++){
			if (empty($list[$i])){
				continue;
			}
			$ret["word"][] = $list[$i];
		}
		//if (!empty($list[$i]) && mb_strlen($list[$i]<5)){
		if (!empty($list[$i])){
			$ret["partial"] = $list[$i];  
		}
		return $ret;
	}
	public static function test(){
		$obj = new QuerySpliter("good morning\tev");
		$ret = $obj->SplitTerm();
		print_r($ret);
		$obj = new QuerySpliter("有中文怎辨\ngood morning\tev\nha");
		$ret = $obj->SplitTerm();
		print_r($ret);
	}
	public static function SimpleUse($query){
		$obj = new QuerySpliter($query);
		$ret = $obj->SplitTerm();
		return $ret;
	}
}
//QuerySpliter::test();
?>
