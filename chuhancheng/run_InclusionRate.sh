#!/bin/bash
# bash run_InclusionRate.sh inputPath/ outputPath/ method

inputPath=$1
outputPath=$2
method=$3

for ((i=100;i>=10;i=i-10))
do
	php run_InclusionRate.php -input $inputPath/"$method"_$i.txt > $outputPath/"$method"_$i.txt
done

php run_InclusionRate.php -input $inputPath/"$method"_all.txt > $outputPath/"$method"_all.txt
