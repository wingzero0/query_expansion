#!/bin/bash
# bash all_TtestRecommendation.sh inputPath/ outputPath/ method
# sample usage:
# bash all_TtestRecommendation.sh Aol_pair_nqq_ttest/ nttest/ completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3
do
	for ((c=1;c<20;c=c+1))
	do
		php run_Recommendation.php -t $t -c $c -input "$inputPath"/"-2.5down.txt" -method $method -o "$outputPath"/independent_"$t"_"$c"/"$method"_-2.5down.txt &
	done
done

