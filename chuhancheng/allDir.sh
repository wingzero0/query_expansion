#!/bin/bash
# bash allDir.sh targetDir

targetDir=$1

for t in 0 1 2 3 
do
	for ((c=1;c<20;c=c+1))
	do 
		#mkdir "$targetDir"/ttest_"$t"_"$c"
		#mkdir "$targetDir"/"$t"_"$c"
		mkdir "$targetDir"/independent_"$t"_"$c"
	done
done
