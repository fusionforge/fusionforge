#!/bin/bash
if [ $# -lt 3 ]; then
    echo "Usage: $0 inputfile elementname outputfile"
    exit 1
fi
if [ ! -f "$1" ]; then
	echo "File $1 does not exist or is not a regular file"
	exit 1
fi

awk '
	BEGIN	 {
		IGNORECASE = 1;
		line="";
		found=0;
		}
	match($0, "</'"$2"'>") {
		print substr($0, 0, RSTART+RLENGTH);
		exit 0}
	{if (found) print;}
	match($0, "<'"$2"'[ |>|/]") {
		found=1;
		line = substr($0, RSTART);
		end = match(line, "</'"$2"'>");
		if (RSTART) {
			print substr(line, 0, RSTART+RLENGTH);
			exit 0
			}
		full = match($0, "<'"$2"'/>");
		if (RSTART) {
			print substr($0, RSTART, RLENGTH);
			exit 0
			}
		print line;
		}
	' $1 >$3
exit 0