#!/bin/bash
# bash run_Recommendation.sh inputPath/ outputPath/ method t c

inputPath=$1
outputPath=$2
method=$3
t=$4
c=$5
entropy=$6

for ((i=100;i>=10;i=i-10))
do
	php run_Recommendation.php -t $t -c $c -input $inputPath/Aol_pair_nqq_$i.$entropy.txt -method $method -o $outputPath/"$method"_$i.$entropy.txt &
	#php run_Recommendation.php -t $t -c $c -input $inputPath/Aol_pair_nqq_$i.$entropy.txt -method $method -o $outputPath/"$method"_$i.$entropy.txt
done
