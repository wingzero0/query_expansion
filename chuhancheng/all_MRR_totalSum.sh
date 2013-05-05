#!/bin/bash
# bash all_MRR_totalSum.sh inputPath/ outputPath/

inputPath=$1
outputPath=$2

## declare an array variable
declare -a methodArr=(baseline completionEntropy completionPure flowandfreq nearestHybrid nearestPure pairandfreq)

for method in ${methodArr[@]}
do
	#rm $outputPath/"$method"_tc_all.txt
	touch $outputPath/"$method"_tc_all.txt
	#rm $outputPath/"$method"_ttest_tc_all.txt
	touch $outputPath/"$method"_ttest_tc_all.txt
	#rm $outputPath/"$method"_independent_tc_all.txt
	touch $outputPath/"$method"_independent_tc_all.txt
	for t in 0 1 2 3
	do
		for ((c=1;c<20;c=c+1))
		do
			cat $inputPath/"$t"_"$c"/"$method"_all.txt >> $outputPath/"$method"_tc_all.txt
			cat $inputPath/ttest_"$t"_"$c"/"$method"_2.5up.txt >> $outputPath/"$method"_ttest_tc_all.txt
			cat $inputPath/independent_"$t"_"$c"/"$method"_all.txt >> $outputPath/"$method"_independent_tc_all.txt
		done
	done
	
	echo 
	echo $method
	php run_MRR.php -input $outputPath/"$method"_tc_all.txt
	#php run_MRR.php -input $outputPath/"$method"_tc_all.txt > $outputPath/"$method".txt
	echo
	echo $method ttest
	php run_MRR.php -input $outputPath/"$method"_ttest_tc_all.txt
	#php run_MRR.php -input $outputPath/"$method"_ttest_tc_all.txt > $outputPath/"$method"_test.txt
	echo
	echo $method independent
	php run_MRR.php -input $outputPath/"$method"_independent_tc_all.txt
	#php run_MRR.php -input $outputPath/"$method"_independent_tc_all.txt > $outputPath/"$method"_independent.txt
	
	rm $outputPath/"$method"_tc_all.txt
	rm $outputPath/"$method"_ttest_tc_all.txt
	rm $outputPath/"$method"_independent_tc_all.txt

done
