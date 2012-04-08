#!/bin/bash
# bash allCatTtest.sh inputPath method

inputPath=$1
method=$2

for t in 0 1 2 3 
do
	for ((c=1;c<20;c=c+1))
	do
		cat "$inputPath"/ttest_"$t"_"$c"/"$method"_200_10.txt > "$inputPath"/ttest_"$t"_"$c"/"$method"_all.txt
		cat "$inputPath"/ttest_"$t"_"$c"/"$method"_10_5.txt >> "$inputPath"/ttest_"$t"_"$c"/"$method"_all.txt
		cat "$inputPath"/ttest_"$t"_"$c"/"$method"_5_2.5.txt >> "$inputPath"/ttest_"$t"_"$c"/"$method"_all.txt
	done
done


