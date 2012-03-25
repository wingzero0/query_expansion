<?php
// Class QueryGoogle will put the query to google search page.
// It can get the num of the page that return from google
// It also can get the recommendation of the original query.

require_once(dirname(__FILE__)."/simple_html_dom.php");

class QueryGoogle{
	private $q;
	private $qs; // split
	public function __construct($query){
		$this->SetQuery($query);
	}
	public function NumOfResults() {
		$html = $this->QueryGooglePage();
		
		$ret = $html->find("div[id=subform_ctrl]");
		if ($ret == null){
			fprintf(STDERR,"can't get body\n");
			return 0;
		}
		//print_r($ret[0]->innertext());
		$html = str_get_html($ret[0]->innertext());
		
		$ret = $html->find("b");
		if ($ret == null){
			fprintf(STDERR,"can't get result\n");
			return 0;
		}
		//print_r($ret[2]->innertext());
		$num =  intval( str_replace(",", "", $ret[2]->innertext()) );
		return $num;
	}
	public function Recommendation($html = null){
		if ($html == null){
			$html = $this->QueryGooglePage();
		}
		$q = $this->FindQuery($html);
		$center_col = $html->find("div[id=center_col]"); // find the main block
		if ($center_col == null){
			fprintf(STDERR,"can't get center_col for query:%s\n", $q);
			return null;
		}
		// search the term "Searches related to"
		$pattern = "Searches related to";
		if ( strstr($center_col[0]->innertext, $pattern) == null ){
			fprintf(STDERR,"no search related for query:%s\n", $q);
			return null;
		}
		
		// there will be two places contain query suggestions
		$flag = false;
		$p = null; 
		$e = $center_col[0]->first_child()->next_sibling();
		if ($e != null){
			// double check for false matching.
			$pattern = "Searches related to";
			if ( strstr($e->innertext, $pattern) == null ){
				$flag = true; // search again
			}else{
				$p = $e->find("p");
				if ($p == null){
					$flag = true; // search again
				}
			}
		}else{
			// $flag can't be true
		}
		
		if ($flag == true){
			$e = $center_col[0]->first_child()->next_sibling()->next_sibling();
			if ($e != null){
				$strHtml = str_get_html($e->outertext());
				$p = $strHtml->find("p");
			}
		}
		//echo $e->outertext()."\n";
		
		if ($p == null){
			fprintf(STDERR,"format error can't get <p></q> for query:%s\n", $q);
			return null;
		}
		$ret["query"] = $q;
		$ret["suggestion"] = array();
		foreach($p as $e){
			$ret["suggestion"][] = $e->plaintext;
		}
		return $ret;
	}
	public function FindQuery($html){
		$ret = $html->find("input[title=Search]"); // find the main block
		if ($ret != null){
			return $ret[0]->value;
		}else{
			return null;
		}
	}
	public function QueryGooglePage(){
		$query = $this->qs[0];
		for ($i = 1;$i<count($this->qs);$i++){
			$query.= "+".$this->qs[$i];
		}
		//$url = "https://www.google.com/search?client=ubuntu&channel=fs&q=pchome&ie=utf-8&oe=utf-8";
		$url = "http://www.google.com/search?q=".$query."&ie=utf-8&oe=utf-8";
		//$url = "http://www.google.com/cse?cx=partner-pub-9300639326172081:5191442144&ie=utf-8&sa=Search&q=" . 
			//$query . "&hl=en&nojs=1";
			
		$html = file_get_html($url);
		return $html;		
	}
	public function DumpHtml(){
		$html = $this->QueryGooglePage();
		echo $html->outertext."\n";
	}
	public function SetQuery($query){
		$this->q = $query;
		$this->qs = mb_split("\s", $this->q);
	}
	public static function test() {
		$obj = new QueryGoogle("");
		$html = file_get_html("./nearestCompletion/661788.html");
		//$numOfResult = $obj->NumOfResults();
		//echo $numOfResult."\n";
		$s = $obj->Recommendation($html);
		print_r($s);
	}
}

//QueryGoogle::test();

?>