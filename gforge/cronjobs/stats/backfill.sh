#!/bin/sh
nb=150
while [ $nb -gt 0 ]
do
	day=`date -d"$nb days ago" +"%Y %m %d"`
	nb=`expr $nb - 1`
	cvsserver/cvs_history_parse.pl $day
	cvsserver/db_stats_cvs_history.pl $day
	echo $day
done

#./db_stats_agg.php -d include_path=/usr/lib/sourceforge/www/include
#./site_stats.php -d include_path=/usr/lib/sourceforge/www/include:.
