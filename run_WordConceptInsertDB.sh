#!/bin/bash
# $1 is cluster start number
# $2 is cluster end number
# $3 is the cluster file path
# $4 is the DB table name

# sample usage
# bash run_WordConceptInsertDB.sh 1 10 output/ WordCluster_test
# above command will put the cluster files in output/ into WordCluster_test table. the cluster files are 1.txt ~ 10.txt

path=$3
table=$4
for((i=$1;i<=$2;i=i+1))
do
	php run_WordConceptInsertDB.php -c $path/$i.txt -TB $table
done
