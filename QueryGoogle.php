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
	public function Recommendation(){
		$html = $this->QueryGooglePage();
		$ret = $html->find("div[id=center_col]");
		if ($ret == null){
			fprintf(STDERR,"can't get center_col\n");
			return 0;
		}
		$e = $ret[0]->first_child()->next_sibling();
		
		$strHtml = str_get_html($e->outertext());
		$ret = $strHtml->find("p");
		if ($ret == null){
			fprintf(STDERR,"format error can't get <p></q>\n");
			return 0;
		}
		$suggestion = array();
		foreach($ret as $e){
			$suggestion[] = $e->plaintext."\n";
		}
		return $suggestion;
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
	public static function test() {
		$obj = new QueryGoogle("pchome");
		//$numOfResult = $obj->NumOfResults();
		//echo $numOfResult."\n";
		$s = $obj->Recommendation();
		print_r($s);
	}
	public function SetQuery($query){
		$this->q = $query;
		$this->qs = mb_split("\s", $this->q);
	}
}

//QueryGoogle::test();

?>