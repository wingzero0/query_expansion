#!/bin/bash
# bash all_Recommendation.sh resultPath sourcePath method

resultPath=$1
sourcePath=$2
method=$3

for t in 0 1 2 3
do
	for ((c=1;c<20;c=c+1))
	do
		php run_SelectIndependentOutput.php -all "$resultPath"/"$t"_"$c"/"$method"_all.txt -d "$sourcePath"/2.5up.txt -o "$resultPath"/independent_"$t"_"$c"/"$method"_all.txt
	done
done
