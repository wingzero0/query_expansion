#!/bin/bash
# bash run_TtestDumpCompletionForMerge.sh inputPath/ outputPrefix/ method
# bash run_TtestDumpCompletionForMerge.sh Aol_pair_nqq_ttest/ completionDiversity/ completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3 
do
	for ((c=1;c<20;c=c+1))
	do
		php run_DumpCompletionForMerge.php -t $t -c $c -input "$inputPath"/2.5up.txt -method $method -o "$outputPath"/ttest_"$t"_"$c"/"$method"_2.5up.txt &
	done
done
