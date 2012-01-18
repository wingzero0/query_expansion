#!/bin/bash
# bash run_InclusionRate.sh inputPath/ outputPath/ method

inputPath=$1
outputPath=$2
method=$3

cat $inputPath/"$method"_100.txt > $outputPath/"$method"_all.txt

for ((i=90;i>=10;i=i-10))
do
	cat $inputPath/"$method"_$i.txt >> $outputPath/"$method"_all.txt
done
