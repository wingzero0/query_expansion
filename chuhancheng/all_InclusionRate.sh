#!/bin/bash
# bash all_InclusionRate.sh inputPath/ outputPath/ method

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3
do
	for ((c=1;c<20;c=c+1))
	do
		#for ((i=100;i>=10;i=i-10))
		#do
		#	php run_InclusionRate.php -input $inputPath/"$t"_"$c"/"$method"_$i.txt > $outputPath/"$t"_"$c"/"$method"_$i.txt
		#done

		php run_InclusionRate.php -input $inputPath/"$t"_"$c"/"$method"_all.txt > $outputPath/"$t"_"$c"/"$method"_all.txt

	done
done
