# !/bin/sh
# -a = --data-only
# -u = use passwd auth
# -f = -file=
# -d = --inserts = not COPY
passwd=`grep sys_dbpasswd /etc/sourceforge/database.inc | cut -d\" -f2`
export passwd
#pg_dump --no-owner --no-reconnect -h `hostname -s` sourceforge -u -a -f db_dump -d <<-FIN
pg_dump --no-owner --no-reconnect -h `hostname -s` sourceforge -u -a -d <<-FIN
sourceforge
$passwd
FIN
