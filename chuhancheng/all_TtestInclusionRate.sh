#!/bin/bash
# bash all_TtestInclusionRate.sh inputPrefix/ outputPrefix/ method
# bash all_TtestInclusionRate.sh version_5_all/ttest InclusionRateScore/ttest completion

inputPath=$1
outputPath=$2
method=$3

for t in 0 1
do
	for c in 1 2 3
	do
		php run_InclusionRate.php -input "$inputPath"_"$t"_"$c"/"$method"_200_10.txt > "$outputPath"_"$t"_"$c"/"$method"_200_10.txt
		php run_InclusionRate.php -input "$inputPath"_"$t"_"$c"/"$method"_10_5.txt > "$outputPath"_"$t"_"$c"/"$method"_10_5.txt
		php run_InclusionRate.php -input "$inputPath"_"$t"_"$c"/"$method"_5_2.5.txt > "$outputPath"_"$t"_"$c"/"$method"_5_2.5.txt
		php run_InclusionRate.php -input "$inputPath"_"$t"_"$c"/"$method"_all.txt > "$outputPath"_"$t"_"$c"/"$method"_all.txt
	done
done
