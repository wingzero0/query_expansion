#!/bin/bash
# bash catTtestAllFile.sh inputPath/ method

inputPath=$1
method=$2
cat $inputPath/"$method"_200_10.txt > $inputPath/"$method"_all.txt
cat $inputPath/"$method"_10_5.txt >> $inputPath/"$method"_all.txt
cat $inputPath/"$method"_5_2.5.txt >> $inputPath/"$method"_all.txt

