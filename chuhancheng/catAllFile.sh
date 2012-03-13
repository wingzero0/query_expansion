#!/bin/bash
# bash catAllFile.sh inputPath/ method

inputPath=$1
method=$2

cat $inputPath/"$method"_100.txt > $inputPath/"$method"_all.txt

for ((i=90;i>=10;i=i-10))
do
	cat $inputPath/"$method"_$i.txt >> $inputPath/"$method"_all.txt
done
