#!/bin/bash
# bash run_TtestInclusionRate.sh inputPath/ outputPath/ method

inputPath=$1
outputPath=$2
method=$3

php run_InclusionRate.php -input $inputPath/"$method"_200_10.txt > $outputPath/"$method"_200_10.txt
php run_InclusionRate.php -input $inputPath/"$method"_10_5.txt > $outputPath/"$method"_10_5.txt
php run_InclusionRate.php -input $inputPath/"$method"_5_2.5.txt > $outputPath/"$method"_5_2.5.txt

php run_InclusionRate.php -input $inputPath/"$method"_all.txt > $outputPath/"$method"_all.txt
