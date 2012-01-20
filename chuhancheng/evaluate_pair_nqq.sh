#!/bin/bash
# evaluate pair_nqq performance

completeTermNum=$1
partialCharNum=$2
method=$3
inputPath=$4
outputPath=$5
versionNum=$6

for ((i=100;i>=30;i=i-10))
do
	mkdir $outputPath/Aol_pair_nqq_$i/
	php evaluate_pair_nqq.php $completeTermNum $partialCharNum $method $inputPath/Aol_pair_nqq_$i.txt $outputPath/Aol_pair_nqq_$i/ $versionNum > $outputPath/score_$i.txt
done
