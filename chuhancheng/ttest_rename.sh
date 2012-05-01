#!/bin/bash

# this shell script will rename the file in ttest directory
# sample usage
# bash ttest_rename.sh path completionDiversity_2.5up.txt.fixed completionDiversity_2.5up.txt

path=$1
oldFile=$2
newFile=$3

for t in 0 1 2 3
do
	for ((c=1;c<20;c=c+1))
	do
		mv "$path"/ttest_"$t"_"$c"/"$oldFile" "$path"/ttest_"$t"_"$c"/"$newFile"
	done
done
