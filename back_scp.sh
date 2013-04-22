#!/bin/bash
# this program will backup the code to kit.csie.ntu.edu.tw

# sample usage
# bash back_scp.sh

server="b95119@ir.csie.ntu.edu.tw:./"


#echo scp "$server""query_expansion/*.php" "./"
#echo scp "$server""query_expansion/*.sh" "./"
#echo scp "$server""query_expansion/chuhancheng/*.php" "./chuhancheng"
#echo scp "$server""query_expansion/chuhancheng/*.sh" "./chuhancheng"
#echo scp "$server""query_expansion/nearestCompletion/*.php" "./nearestCompletion"
#echo scp "$server""query_expansion/nearestCompletion/*.sh" "./nearestCompletion"
#echo scp "$server""public_html/chartjs/InclusionRate/*.php" "./chartjs/"
#echo scp "$server""public_html/chartjs/MRRScore/*.php" "./chartjs/"
#echo scp "$server""public_html/userStudy/*" "./userStudy/"

scp "$server""query_expansion/*.php" "./"
scp "$server""query_expansion/*.sh" "./"
scp "$server""query_expansion/chuhancheng/*.php" "./chuhancheng"
scp "$server""query_expansion/chuhancheng/*.sh" "./chuhancheng"
scp "$server""query_expansion/nearestCompletion/*.php" "./nearestCompletion"
scp "$server""query_expansion/nearestCompletion/*.sh" "./nearestCompletion"
scp "$server""public_html/chartjs/InclusionRate/*.php" "./chartjs/"
scp "$server""public_html/chartjs/MRRScore/*.php" "./chartjs/"
scp "$server""public_html/userStudy/*" "./userStudy/"

