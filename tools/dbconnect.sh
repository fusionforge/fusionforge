# !/bin/sh
passwd=`grep sys_dbpasswd /etc/sourceforge/database.inc | cut -d\" -f2`
echo $passwd
psql -U sourceforge -h `hostname -s` sourceforge
