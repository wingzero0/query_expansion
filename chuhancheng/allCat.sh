#!/bin/bash
# bash allCat.sh inputPathPrefix outputPathPrefix method

inputPath=$1
outputPath=$2
method=$3

for t in 0 1
do 
	for c in 1 2 3
	do 
		cat $inputPath/"$t"_"$c"/"$method"_100.txt > $outputPath/"$t"_"$c"/"$method"_all.txt

		for ((i=90;i>=10;i=i-10))
		do
			cat $inputPath/"$t"_"$c"/"$method"_"$i".txt >> $outputPath/"$t"_"$c"/"$method"_all.txt
		done

	done
done
