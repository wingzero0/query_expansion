#!/bin/bash
# bash all_IndependentInclusionRate.sh inputPrefix/ outputPrefix/ method
# bash all_IndependentInclusionRate.sh tmpOutput/ InclusionRateScore/ completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3 
do
	for ((c=1;c<20;c=c+1))
	do
		php run_InclusionRate.php -input "$inputPath"/independent_"$t"_"$c"/"$method"_all.txt > "$outputPath"/independent_"$t"_"$c"/"$method"_all.txt
	done
done
