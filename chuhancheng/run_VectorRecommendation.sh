#!/bin/bash
# bash run_VectorRecommendation.sh inputPath/ outputPath/ method

inputPath=$1
outputPath=$2
method=$3

t=0
for ((c=1;c<=2;c=c+1))
do
	for ((i=1;i<=4;i=i+1))
	do
		php run_Recommendation.php -t $t -c $c -input $inputPath/tmp."$i".txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_"$i".txt &
	done
done
