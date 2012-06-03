#!/bin/bash
# bash NoHybrid0_1.sh sourcePath outputPath method
# bash nearest0_y.sh Aol_pair_nqq2 tmpOutput nearest 1 4

inputPath=$1
outputPath=$2
method=$3
starti=$4
endi=$5

for c in 1 2
do
	for ((i=$starti;i<=$endi;i=i+1))
	do 
		php run_Recommendation.php -t 0 -c $c -input $inputPath/Alldata.txt."$i" -method $method -o $outputPath/0_"$c"/"$method"_"$i".txt &
		#echo run_Recommendation.php -t 0 -c $c -input $inputPath/Alldata.txt."$i" -method $method -o $outputPath/0_"$c"/"$method"_"$i".txt &
	done
done
