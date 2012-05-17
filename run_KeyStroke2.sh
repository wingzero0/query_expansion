#!/bin/bash
# bash run_KeyStroke2.sh inputPath/ outputPath/
# sample usage:
# bash run_KeyStroke2.sh chuhancheng/Aol_pair_nqq2/ chuhancheng/KeyStrokeStatistics/ completion

inputPath=$1
outputPath=$2
method=$3

for ((i=1;i<=6;i=i+1))
do
	file="Alldata.""$i"".txt"
	php run_KeyStroke.php -i "$inputPath"/"$file" -o "$outputPath"/"$file" -m $method &
done