#!/bin/bash
# this program will backup the code to kit.csie.ntu.edu.tw

# sample usage
# bash scp.sh

server="ir.csie.ntu.edu.tw:./"


#echo "$server""query_expansion/*.php" "./"
#echo "$server""query_expansion/*.sh" "./"
#echo "$server""query_expansion/chuhancheng/*.php" "./chuhancheng"
#echo "$server""query_expansion/chuhancheng/*.sh" "./chuhancheng"
#echo "$server""query_expansion/nearestCompletion/*.php" "./nearestCompletion"
#echo "$server""query_expansion/nearestCompletion/*.sh" "./nearestCompletion"
#echo "$server""public_html/chartjs/InclusionRate/*.php" "./chartjs/"
#echo "$server""public_html/userStudy/*" "./userStudy/"

scp "$server""query_expansion/*.php" "./"
scp "$server""query_expansion/*.sh" "./"
scp "$server""query_expansion/chuhancheng/*.php" "./chuhancheng"
scp "$server""query_expansion/chuhancheng/*.sh" "./chuhancheng"
scp "$server""query_expansion/nearestCompletion/*.php" "./nearestCompletion"
scp "$server""query_expansion/nearestCompletion/*.sh" "./nearestCompletion"
scp "$server""public_html/chartjs/InclusionRate/*.php" "./chartjs/"
scp "$server""public_html/userStudy/*" "./userStudy/"

