#!/bin/bash
# bash run_TtestRecommendation.sh inputPath/ outputPath/ method t c

inputPath=$1
outputPath=$2
method=$3
t=$4
c=$5

php run_Recommendation.php -t $t -c $c -input $inputPath/200_10.txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_200_10.txt &
php run_Recommendation.php -t $t -c $c -input $inputPath/10_5.txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_10_5.txt &
php run_Recommendation.php -t $t -c $c -input $inputPath/5_2.5.txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_5_2.5.txt &
#php run_Recommendation.php -t $t -c $c -input $inputPath/2.5_0.txt -method $method -o $outputPath/2.5_0.txt &

