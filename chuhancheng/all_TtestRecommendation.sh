#!/bin/bash
# bash all_TtestRecommendation.sh inputPath/ outputPath/ method
# sample usage:
# bash all_TtestRecommendation.sh Aol_pair_nqq_ttest/ version_5_all/ completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1
do
	for c in 1 2 3
	do
		php run_Recommendation.php -t $t -c $c -input $inputPath/200_10.txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_200_10.txt &
		php run_Recommendation.php -t $t -c $c -input $inputPath/10_5.txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_10_5.txt &
		php run_Recommendation.php -t $t -c $c -input $inputPath/5_2.5.txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_5_2.5.txt &
	done
done
#php run_Recommendation.php -t $t -c $c -input $inputPath/2.5_0.txt -method $method -o $outputPath/2.5_0.txt &

