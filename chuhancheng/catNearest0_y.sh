#!/bin/bash
# bash catNearest0_y.sh inputPath/ method

inputPath=$1
method=$2

for ((y=1;y<=2;y=y+1))
do
	cat $inputPath/0_"$y"/nearest_1.txt > $inputPath/0_"$y"/"$method"_all.txt
	for ((i=2;i<=24;i=i+1))
	do
		cat $inputPath/0_"$y"/nearest_"$i".txt >> $inputPath/0_"$y"/"$method"_all.txt
	done
done
