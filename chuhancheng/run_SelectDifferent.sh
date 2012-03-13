#!/bin/bash
#

inPath=$1
outPath=$2

php run_SelectDifferent.php -f1 $inPath/baseline_all.txt -f2 $inPath/completion_all.txt -o1 $outPath/baseline_r.txt -o2 $outPath/completion_r.txt
