#!/bin/bash

# this shell script will remove the method_suffix in the dir tree

path=$1
file1=$2
file2=$3

for t in 0 1 2 3
do
	for ((c=2;c<20;c=c+1))
	do
		fullPath="$path"/ttest_"$t"_"$c"
		#echo php SyncOutputOrder "$fullPath"/"$file1" "$fullPath"/"$file2"
		php SyncOutputOrder.php "$fullPath"/"$file1" "$fullPath"/"$file2"
	done
done
