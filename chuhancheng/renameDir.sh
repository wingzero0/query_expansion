#!/bin/bash
#rename the suggestion dir 
#sample usage
# bash renameDir.sh ttest clean_ttest


oPrefix=$1
nPrefix=$2

for t in 0 1 2 3
do
	for ((c=1;c<20;c=c+1))
	do
		#echo "$oPrefix"_"$t"_"$c"/ to "$nPrefix"_"$t"_"$c"/
		mv "$oPrefix"_"$t"_"$c"/ "$nPrefix"_"$t"_"$c"/
	done
done
