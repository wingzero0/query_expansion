<?php
// Class QueryGoogle will put the query to google search page.
// It can get the num of the page that return from google

require_once("simple_html_dom.php");

class QueryGoogle{
	public function __construct($query){
		$this->q = $query;
		$this->qs = mb_split("\s", $this->q);
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
	public function QueryGooglePage(){
		$query = $this->qs[0];
		for ($i = 1;$i<count($this->qs);$i++){
			$query.= "+".$this->qs[$i];
		}
		$url = "http://www.google.com/search?q=".$query."&ie=utf-8&oe=utf-8";
		//$url = "http://www.google.com/cse?cx=partner-pub-9300639326172081:5191442144&ie=utf-8&sa=Search&q=" . 
			//$query . "&hl=en&nojs=1";
			
		$html = file_get_html($url);
		return $html;		
	}
	public static function test() {
		$obj = new QueryGoogle("ntu csie");
		$numOfResult = $obj->NumOfResults();
		echo $numOfResult."\n";
	}
}

//QueryGoogle::test();

?>