<?php
// class KeyStroke want to simulate the process of a uesr entering the query.
// it counts the number of key inputting to the web.
// it also assume the user see a dynamic suggestion and select the suggestion to save the key stroke.

require_once("QuerySpliter.php");
require_once("run_QueryCompletionAPI.php");
define("SUCCESS", 1);
define("CONT",2);

class KeyStroke{
	protected $spliter;
	public function __construct($q1, $q2){
		$this->mTermPool = array(); // match term pool
		$this->uTermPool = array(); // un-match term pool
		$this->uTermOrder = array(); // un-match term pool
		$this->q1 = $q1;

		$pattern = "/(\s)+/";
		$this->q2Temp = preg_replace($pattern , " ", $q2);

		$this->spliter = new QuerySpliter($q2);
		$this->spliter->SplitTerm();
		$list = $this->spliter->qSegment;
		$this->uTermOrder = $list;
		foreach ($list as $i => $term){
			if ( !isset($this->uTermPool[$term]) ){
				$this->uTermPool[$term] = 0;
			}
			$this->uTermPool[$term] += 1;
			$this->mTermPool[$term] = 0;
			//$this->uTermInvertedIndex[$term][] = $i; // the term maybe duplicate
		}
		//$thi->uTermOrder = $list;
	}
	private function ChangeTermPool($terms){//change the term from unmatch to match
		//echo "in ChangeTermPool:\n";
		//print_r($terms);
		for ($i = 0;$i < count($terms); $i++){
			$this->uTermPool[$terms[$i]] -=1;
			$this->mTermPool[$terms[$i]] +=1;
			foreach($this->uTermOrder as $index => $term){
				if ( $term == $terms[$i] ){
					unset($this->uTermOrder[$index]);
					break;
				}
			}

			if ($this->uTermPool[$terms[$i]] == 0){
				unset($this->uTermPool[$terms[$i]]);
			}
		}

		$this->uTermOrder = $this->ResetIndex($this->uTermOrder);
		if ( empty($this->uTermOrder) ){
			return SUCCESS;
		}else{
			return CONT;
		}
	}
	private function ResetIndex($numbericArray){
		$newOrder = array();
		foreach ($numbericArray as $index => $term){
			$newOrder[] = $term;
		}
		return $newOrder;
	}
	protected function MatchingCount($suggestion){// newTerm is a new matching term
		//echo "in MatchingCount:suggestion:".$suggestion."\n";
		$this->spliter->ReplaceNewQuery($suggestion);
		$this->spliter->SplitTerm();
		$list = $this->spliter->qSegment;
		//print_r($list)."\n";
		$mtmp = array(); // match tmp
		$utmp = array(); // un match tmp
		foreach ($this->mTermPool as $term => $num){
			$mtmp[$term] = $num;
		}
		foreach ($this->uTermPool as $term => $num){
			$utmp[$term] = $num;
		}

		$newTerms = array();
		$newWord = 0;
		//echo "mtmp:\n";
		//print_r($mtmp);
		//echo "\nutmp:\n";
		//print_r($utmp);
		//echo "\n";
		foreach ($list as $i => $term){
			if ( !isset($mtmp[$term]) || $mtmp[$term] <= 0){
				if ( isset($utmp[$term]) && $utmp[$term] >0 ){
					// the new term is in grouth truth
					$utmp[$term] -=1;
					$newTerms[] = $term;
					$newWord++;
				}else{
					// some term is not in groud truth
					$newWord = 0;
					$newTerms = null;
					break;
				}
			}else{
				$mtmp[$term] -=1;
			}
		}
		$ret["count"] = $newWord;
		$ret["newTerms"] = $newTerms;
		//echo "in MatchingCount:suggestion:\n";
		//print_r($ret);
		//echo "\n";
		return $ret;
	}
	protected function SelectSuggestion($partialQ){
		// get suggestions
		$completionArray = run_QueryCompletion($this->q1, $partialQ, 5);
		$max = 0;
		$ret = array();
		$i = 0;
		//select maximun matching Q
		//print_r($completionArray);
		foreach($completionArray["prob"] as $newQ => $prob){
			//echo "\t".$newQ."\n";
			if ($i >= 10){
				break;
			}
			$matchingRet = $this->MatchingCount($newQ);
			if ($max < $matchingRet["count"]){
				$max = $matchingRet["count"];
				$ret["newTerms"] = $matchingRet["newTerms"];
				$ret["newQ"] = $newQ;
			}
			$i++;
		}
		return $ret;
	}
	protected function GetNextCharacter($newMatchingTerms, $first = false){
		if ($first == true){
			$this->lastTypping = $this->uTermOrder[0];
			$this->lastPos = 0;
			return substr($this->lastTypping, 0, 1);
		}
		if ( empty($newMatchingTerms) ){
			// get a letter from the query term of last iteration selected
			$this->lastPos+=1;
			if ($this->lastPos < strlen($this->lastTypping)) {
				// select next character
				return substr($this->lastTypping, $this->lastPos, 1);
			}else{
				// select next term
				unset($this->uTermOrder[0]);
				$this->uTermOrder = $this->ResetIndex($this->uTermOrder);
				if ( empty($this->uTermOrder) ){
					// the typping process is end.
					return "";
				}
				$this->lastTypping = $this->uTermOrder[0];
				$this->lastPos = 0;
				return " ".substr($this->lastTypping, $this->lastPos, 1); //has a space
			}
		}else{
			$ret = $this->ChangeTermPool($newMatchingTerms);
			if ($ret == SUCCESS){
				return "";
			}
			$this->lastTypping = $this->uTermOrder[0]; // uTermOrder should not be empty
			if ( empty($this->uTermOrder) ){
				return "";
			}
			$this->lastPos = 0;
			return " ".substr($this->uTermOrder[0], 0, 1);
		}
	}
	public function SimulateUserTyping(){
		//$flag = true;
		$nextC = $this->GetNextCharacter(array(),true); // first get
		$partialQ = $nextC;
		$ret["typing"] = 0;
		$ret["selection"] = 0;

		while ( !empty($nextC) ){
			$ret["typing"] +=1;
			//echo "partialQ:'".$partialQ."' nextC:'".$nextC."'\n";
			$suggestionRet = $this->SelectSuggestion($partialQ);
			//echo "in simulateUserTyping\tsuggestionRet:\n";
			//print_r($suggestionRet);
			if ( empty($suggestionRet) ){
				$nextC = $this->GetNextCharacter(array());
			}else{
				$partialQ = $suggestionRet["newQ"];
				$nextC = $this->GetNextCharacter($suggestionRet["newTerms"]);
				$ret["selection"] +=1;
			}
			$partialQ = $partialQ . $nextC;
		}
		return $ret;
	}
	public static function test(){
		//$obj = new KeyStroke("gucci","neiman marcus");
		$obj = new KeyStroke("gucci","neiman cannotmatch cars");
		//$obj = new KeyStroke("mapquest", "yahoo maps");
		$keyCount = $obj->SimulateUserTyping();
		//print_r($keyCount);
	}
}

//KeyStroke::test();


?>
