#!/bin/bash
# bash allCatTtest.sh inputPathPrefix outputPathPrefix method

inputPath=$1
outputPath=$2
method=$3

for t in 0 1
do
	for c in 1 2 3
	do
		cat "$inputPath"_"$t"_"$c"/"$method"_200_10.txt > "$outputPath"_"$t"_"$c"/"$method"_all.txt
		cat "$inputPath"_"$t"_"$c"/"$method"_10_5.txt >> "$outputPath"_"$t"_"$c"/"$method"_all.txt
		cat "$inputPath"_"$t"_"$c"/"$method"_5_2.5.txt >> "$outputPath"_"$t"_"$c"/"$method"_all.txt
	done
done


