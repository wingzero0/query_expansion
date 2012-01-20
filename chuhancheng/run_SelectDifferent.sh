#!/bin/bash
#

path1=$1
path2=$2
outpath=$3

for ((i=100;i>=30;i=i-10))
do
	php run_SelectDifferent.php -f1 $path1/Aol_pair_nqq_$i/match_0_2.txt -f2 $path2/Aol_pair_nqq_$i/match_0_2.txt -o1 $outpath/copmletion_$i.txt -o2 $outpath/baseline_$i.txt
done
