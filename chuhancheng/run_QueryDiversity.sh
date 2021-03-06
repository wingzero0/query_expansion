#!/bin/bash
# bash run_QueryDiversity.sh inputPrefix/ outputPrefix/ method
# bash run_QueryDiversity.sh completionDiversity/ version_5_all/ completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3 
do
	for ((c=1;c<20;c=c+1))
	do
		php ../run_QueryDiversity.php -vTb NgramVector -i "$inputPath"/"$t"_"$c"/"$method"_all.txt -o "$outputPath"/"$t"_"$c"/"$method"Diversity_all.txt & 
	done
done
