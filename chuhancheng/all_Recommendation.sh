#!/bin/bash
# bash all_Recommendation.sh inputPath outputPath method

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3
do
	for ((c=1;c<20;c=c+1))
	do
		php run_Recommendation.php -t $t -c $c -input $inputPath/Aol_pair_nqq_all.txt -method $method -o $outputPath/"$t"_"$c"/"$method"_all.txt &
	#	for ((i=100;i>=10;i=i-10))
	#	do
	#		php run_Recommendation.php -t $t -c $c -input $inputPath/Aol_pair_nqq_$i.txt -method $method -o $outputPath/"$t"_"$c"/"$method"_$i.txt &
	#	done
	done
done
