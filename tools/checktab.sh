#! /bin/sh
for file in $@
do 
	echo "================= Bad Record Begin For $file  ================="
	cat $file | grep -v '^.[^ 	]*	.[^ 	]*	' | grep -v '^#'
	echo "================= Bad Record End ================="
done
