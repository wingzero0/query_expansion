#!/bin/bash
# bash NoHybrid0_1.sh sourcePath outputPath method
# bash NoHybrid0_1.sh Aol_pair_nqq2 NoHybrid nearest

inputPath=$1
outputPath=$2
method=$3

for i in 1 2 3 4 5 6
do 
	php run_Recommendation.php -t 0 -c 1 -input $inputPath/tmp."$i".txt -method $method -o $outputPath/0_1/"$method"_"$i".txt &
done
