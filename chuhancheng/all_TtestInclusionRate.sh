#!/bin/bash
# bash all_TtestInclusionRate.sh inputPrefix/ outputPrefix/ method
# bash all_TtestInclusionRate.sh version_5_all/ InclusionRateScore/ completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1 2 3 
do
	for ((c=1;c<20;c=c+1))
	do
		#		php run_InclusionRate.php -input "$inputPath"/ttest_"$t"_"$c"/"$method"_200_10.txt > "$outputPath"/ttest_"$t"_"$c"/"$method"_200_10.txt
		#		php run_InclusionRate.php -input "$inputPath"/ttest_"$t"_"$c"/"$method"_10_5.txt > "$outputPath"/ttest_"$t"_"$c"/"$method"_10_5.txt
		#		php run_InclusionRate.php -input "$inputPath"/ttest_"$t"_"$c"/"$method"_5_2.5.txt > "$outputPath"/ttest_"$t"_"$c"/"$method"_5_2.5.txt
		#		php run_InclusionRate.php -input "$inputPath"/ttest_"$t"_"$c"/"$method"_all.txt > "$outputPath"/ttest_"$t"_"$c"/"$method"_all.txt
		php run_InclusionRate.php -input "$inputPath"/ttest_"$t"_"$c"/"$method"_2.5up.txt > "$outputPath"/ttest_"$t"_"$c"/"$method"_all.txt
	done
done
