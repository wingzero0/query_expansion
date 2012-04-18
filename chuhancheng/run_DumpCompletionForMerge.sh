#!/bin/bash
# bash run_DumpCompletionForMerge.sh inputPath/ outputPrefix/ method
# bash run_DumpCompletionForMerge.sh Aol_pair_nqq2/ completionDiversity/ completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3 
do
	for ((c=1;c<20;c=c+1))
	do
		php run_DumpCompletionForMerge.php -t $t -c $c -input "$inputPath"/Aol_pair_nqq_all.txt -method $method -o "$outputPath"/"$t"_"$c"/"$method"_all.txt &
	done
done
