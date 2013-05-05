#!/bin/bash
# bash all_MRR.sh inputPath/ outputPath/ method

inputPath=$1
outputPath=$2
#method=$3
## declare an array variable
declare -a methodArr=(baseline completionEntropy completionPure flowandfreq nearestHybrid nearestPure pairandfreq)

: <<'END'
for method in ${methodArr[@]}
do
	for t in 0 1 2 3
	do
		for ((c=1;c<20;c=c+1))
		do
			php run_MRR.php -input $inputPath/"$t"_"$c"/"$method"_all.txt > $outputPath/"$t"_"$c"/"$method".txt
			php run_MRR.php -input $inputPath/ttest_"$t"_"$c"/"$method"_2.5up.txt > $outputPath/ttest_"$t"_"$c"/"$method".txt
			php run_MRR.php -input $inputPath/independent_"$t"_"$c"/"$method"_all.txt > $outputPath/independent_"$t"_"$c"/"$method".txt
		done
	done
done
END

for t in 0 1 2 3
do
	for ((c=1;c<20;c=c+1))
	do
		printf "%i_%i\t" $t $c
		for method in ${methodArr[@]}
		do
			printf "%s\t " $method
			php run_MRR.php -input $inputPath/"$t"_"$c"/"$method"_all.txt
			printf "\t"
		done
		printf "\n"
	done
done
