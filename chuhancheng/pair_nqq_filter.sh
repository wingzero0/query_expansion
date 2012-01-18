#!/bin/bash
# generate seperate pair qnn txt

versionNum=$1

php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_100.txt 100 91 QueryCluster_$versionNum
php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_90.txt 90 81 QueryCluster_$versionNum
php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_80.txt 80 71 QueryCluster_$versionNum
php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_70.txt 70 61 QueryCluster_$versionNum
php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_60.txt 60 51 QueryCluster_$versionNum
php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_50.txt 50 41 QueryCluster_$versionNum
php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_40.txt 40 31 QueryCluster_$versionNum
php pair_nqq_filter.php Aol_pair_nqq.txt Aol_pair_nqq_30.txt 30 21 QueryCluster_$versionNum
