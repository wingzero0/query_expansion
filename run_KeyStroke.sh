#!/bin/bash
# bash run_KeyStroke.sh inputPath/ outputPath/
# sample usage:
# bash run_KeyStroke.sh chuhancheng/Aol_pair_nqq_ttest/ chuhancheng/KeyStrokeStatistics/ completion

inputPath=$1
outputPath=$2
method=$3

for file in "tmp.1.txt" "tmp.2.txt" "tmp.3.txt" "tmp.4.txt"
do
	php run_KeyStroke.php -i "$inputPath"/"$file" -o "$outputPath"/"$file" -m $method &
	#php run_KeyStroke.php -i "$inputPath"/"$file" -o "$outputPath"/"$file" &
done