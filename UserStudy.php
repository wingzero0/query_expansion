<?php
// this class want to prepare the data of completionMethod and 
// let the user rate the performance of the method.
// It will read the snippy of q1 and different completion of q2 in different 
// setting.
//
require_once(dirname(__FILE__)."/QueryGoogle.php");

class UserStudy{
	protected $snippyPool;
	protected $snippyPath;
	public function __construct($q1, $q2, $snippyPath, $resultPath, $resultPool){
		// resultPath is the select result
		// resultPool is the file that many results joined together.
		$this->q1 = $q1;
		$this->q2 = $q2;
		$this->queryGoogle = new QueryGoogle($q1);
		$this->snippyPath = $snippyPath;
		$this->resultPath = $resultPath;
		$this->resultPool = $resultPool; 
	}
	public function InitSnippyPool($dir){
		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($filename = readdir($dh)) !== false) {
					$filePath = $dir. "/". $filename;
					if ( filetype($filePath) == "file"){
						$content = file_get_contents($filename);
						if ($content != false){
							$this->snippyPool[$filename] = $content;
						}
					}
				}
				closedir($dh);
			}else{
				fprintf(STDERR, "%s can't not open\n", $dir);
			}
		}else{
			fprintf(STDERR, "%s is not dir\n", $dir);
		}
	}
	public function FindSnippyOnDisk($q){
		$file = $this->snippyPath . "/" . $q . ".txt";
		$content = null;
		if (file_exists($file)){
			$content = file_get_contents($file);
		}
		return $content;
	}
	public function GetQSnippy($q){
		$content = $this->FindSnippyOnDisk($q);
		//echo "get snippy\n";
		if ( $content ){
			return $content;
		}else{
			//echo "test\n";
			$this->queryGoogle->SetQuery($q);
			$centerCol = $this->queryGoogle->GetCenterCol();
			$fp = fopen($this->snippyPath. "/" . $q . ".txt", "w");
			if ($fp == null){
				fprintf(STDERR, "%s can be save\n", $q);
				//echo "NOT save\n";
			}else {
				fprintf($fp, "%s", $centerCol);
				fclose($fp);
			}
			return $centerCol;
		}
	}
	public function FindCompletionOnDisk($method, $t,$c){
		$file = $this->resultPath . "/". $t ."_". $c. "/" . $this->q1 ."_". $this->q2 ."_". 
			$method . ".txt";
		if ( !file_exists($file)){
			return array();
		}
		$fp = fopen($file, "r");
		if ($fp == null){
			return array(); // empty array;
		}
		$ret = array();
		while ( $line = fgets($fp) ){
			$ret[] = trim($line);
		}
		fclose($fp);
		return $ret;
	}
	public function FindCompletionInPool($method, $t,$c){
		$poolFile = $this->resultPool . "/". $t ."_". $c. "/" . $method . "_all.txt";
		$fp = fopen($poolFile, "r");
		if ($fp == null){
			return null;
		}
		$ret = array();
		$pattern = "/".$this->q1."\t".$this->q2."/";
		while ( $line = fgets($fp) ){
			$line = trim($line);
			$hit = preg_match($pattern, $line, $matches);
			if ($hit > 0){
				while($line = fgets($fp)){
					$line = trim($line);
					$list = split("\t", $line);
					//print_r($list);
					if ( count($list) == 1 ){					
						$ret[] = $list[0];
					}else{
						break;
					}
				}
				break;
			}
		}
		fclose($fp);
		return $ret;
	}
	public function SaveCompletion($method, $t,$c, $suggestion){
		$saveFile = $this->resultPath . "/". $t ."_". $c. "/" . $this->q1 ."_". $this->q2 ."_". 
			$method . ".txt";
		$fp = fopen($saveFile, "w");
		if ($fp == null){
			fprintf(STDERR, "%s can be save\n", $saveFile);
		}else {
			for ($i = 0;$i < count($suggestion);$i++){
				fprintf($fp, "%s\n", $suggestion[$i]);
			}
			fclose($fp);
		}
	}
	public function GetCompletion($method, $t, $c){
		$ret = $this->FindCompletionOnDisk($method, $t,$c);
		if ( $ret ){
			return $ret;
		}else{
			$suggestion = $this->FindCompletionInPool($method, $t,$c);
			fprintf(STDERR, "saving suggestion\n");
			$this->SaveCompletion($method,$t,$c, $suggestion);
			return $suggestion;
		}
	}
	public function GeneratePartialQ2($t,$c){
		$q2_term_array = explode(" ",$this->q2);

		if($t >= count($q2_term_array)){ // $t is the number of completion term
			return null;
		}else if ( $c >= strlen($q2_term_array[$t]) ){
			return null;
		}else{
			$partailQ="";
			for($i=0; $i<$t;$i++){
				$partailQ = $partailQ." ".$q2_term_array[$i];
			}
			$partailQ = $partailQ." ".substr($q2_term_array[$t],0,$c); // $c is the number of partial character
			$partailQ = trim($partailQ);
		}
		return $partailQ;
	}
	//public function GetResult() 
}


?>
