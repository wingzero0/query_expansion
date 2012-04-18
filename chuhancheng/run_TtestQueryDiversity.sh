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
		php ../run_QueryDiversity.php -vTb NgramVector -i "$inputPath"/ttest_"$t"_"$c"/"$method"_2.5up.txt -o "$outputPath"/ttest_"$t"_"$c"/"$method"Diversity_2.5up.txt & 
	done
done
