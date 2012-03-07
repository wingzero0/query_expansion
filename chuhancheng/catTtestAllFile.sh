#!/bin/bash
# bash catTtestAllFile.sh inputPath/ outputPath/ method

inputPath=$1
outputPath=$2
method=$3
cat $inputPath/"$method"_200_10.txt > $outputPath/"$method"_all.txt
cat $inputPath/"$method"_10_5.txt >> $outputPath/"$method"_all.txt
cat $inputPath/"$method"_5_2.5.txt >> $outputPath/"$method"_all.txt

