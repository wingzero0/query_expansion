#!/bin/bash
# bash run_InclusionRate.sh inputPath/ outputPath/ method entropy

inputPath=$1
outputPath=$2
method=$3
entropy=$4
for ((i=100;i>=10;i=i-10))
do
	php run_InclusionRate.php -input $inputPath/"$method"_$i.$entropy.txt > $outputPath/"$method"_$i.$entropy.txt
done

php run_InclusionRate.php -input $inputPath/"$method"_all.$entropy.txt > $outputPath/"$method"_all.$entropy.txt
