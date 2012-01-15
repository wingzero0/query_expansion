<?php
//require_once(dirname(__FILE__)."/connection.php");
//mysql_select_db($database_cnn,$b95119_cnn);

class SelectDifferent{
	public $f1;
	public $f2;
	public $fp1;
	public $fp2;
	public $ofp1;
	public $ofp2;
	public $data1;
	public $data2;
	public function __construct($file1, $file2, $outfile1, $outfile2) {
		$this->f1 = $file1;
		$this->fp1 = fopen($file1, "r");
		if ($this->fp1 == NULL){
			fprintf(STDERR, "file1 %s opened error\n", $file1);
		} 
		$this->f2 = $file2;
		$this->fp2 = fopen($file2, "r");
		if ($this->fp2 == NULL){
			fprintf(STDERR, "file2 %s opened error\n", $file2);
		}
		
		$this->ofp1 = fopen($outfile1, "w");
		if ($this->ofp1 == NULL){
			fprintf(STDERR, "file2 %s opened error\n", $outfile1);
		}
		$this->ofp2 = fopen($outfile2, "w");
		if ($this->ofp2 == NULL){
			fprintf(STDERR, "file2 %s opened error\n", $outfile2);
		} 
	}
	protected function Load(){
		while ($line = fgets($this->fp1)){
			$line = trim($line);
			$list = preg_split("/\t/", $line);
			if (count($list) < 3){
				continue;
			}
			$this->data1[$list[0]][$list[1]][$list[2]] = true;
		}
		while ($line = fgets($this->fp2)){
			$line = trim($line);
			$list = preg_split("/\t/", $line);
			if (count($list) < 3){
				continue;
			}
			$this->data2[$list[0]][$list[1]][$list[2]] = true;
		}
	}
	public function FindDifferent(){
		$this->Load();
		foreach ($this->data1 as $num => $v1){
			foreach ($v1 as $w1 => $v2){
				foreach ($v2 as $w2 => $v3){
					if ( !isset($this->data2[$num][$w1][$w2]) ){
						fprintf($this->ofp1, "%s\t%s\t%s\n", $num, $w1, $w2);
					}
				}
			}
		}
		foreach ($this->data2 as $num => $v1){
			foreach ($v1 as $w1 => $v2){
				foreach ($v2 as $w2 => $v3){
					if ( !isset($this->data1[$num][$w1][$w2]) ){
						fprintf($this->ofp2, "%s\t%s\t%s\n", $num, $w1, $w2);
					}
				}
			}
		}
	}
}
?>