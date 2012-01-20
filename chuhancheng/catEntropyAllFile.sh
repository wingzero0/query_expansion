#!/bin/bash
# bash run_InclusionRate.sh inputPath/ outputPath/ method entropy

inputPath=$1
outputPath=$2
method=$3
entropy=$4
cat $inputPath/"$method"_100.$entropy.txt > $outputPath/"$method"_all.$entropy.txt

for ((i=90;i>=10;i=i-10))
do
	cat $inputPath/"$method"_$i.$entropy.txt >> $outputPath/"$method"_all.$entropy.txt
done
