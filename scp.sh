#!/bin/bash
# this program will backup the code to kit.csie.ntu.edu.tw

# sample usage
# bash scp.sh

scp *.php *.sh kit.csie.ntu.edu.tw:./query_expansion/
scp ./chuhancheng/*.php ./chuhancheng/*.sh kit.csie.ntu.edu.tw:./query_expansion/chuhancheng/
scp ./nearestCompletion/*.php ./nearestCompletion/*.sh kit.csie.ntu.edu.tw:./query_expansion/nearestCompletion/
scp ~/public_html/chartjs/InclusionRate/*.php kit.csie.ntu.edu.tw:./query_expansion/chartjs/
