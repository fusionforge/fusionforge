# !/bin/sh

createlist()
{
echo "Creating $1 List"
psql -U sourceforge -h `hostname -s` sourceforge 2>&1 > $DESCDIR/$1 <<-FIN
$DBPASS
\\$2
FIN
cat $DESCDIR/$1 | grep "^ [a-z]" | sed "s/^ \([a-z].[^ |]*\). *|.*/\1/" | sort -u > $DESCDIR/$1.tmp
mv $DESCDIR/$1.tmp $DESCDIR/$1
[ ! -d $DESCDIR/$1.dump ] && mkdir $DESCDIR/$1.dump
#cat  $DESCDIR/$1 | while read tablename
#do
#	pg_dump --no-owner --no-reconnect -h `hostname -s` sourceforge -u -c -d -t $tablename -f $DESCDIR/$1.dump/$tablename <<-FIN
#sourceforge
#$DBPASS
#FIN
#done
}

[ "x$DESCDIR" == "x" ] && DESCDIR=desc
echo "Desc in $DESCDIR"
[ "x$DBPASS" == "x" ] && DBPASS=`grep sys_dbpasswd /etc/sourceforge/database.inc| cut -d\" -f2 ` && [ "x$DBPASS" == "x" ] && echo "Can't get DB Passwd" && exit
export DBPASS

[ ! -d $DESCDIR ] && mkdir $DESCDIR

createlist tables dt
createlist indices di
createlist sequences ds
createlist views dv

