# !/bin/sh
# -a = --data-only
# -u = use passwd auth
# -f = -file=
# -d = --inserts = not COPY
[ ! -d desc ] && mkdir desc
passwd=`grep sys_dbpasswd /etc/sourceforge/database.inc | cut -d\" -f2`
export passwd

echo "Creating tables List"
psql -U sourceforge -h `hostname -s` sourceforge 2>&1 > desc/tables <<-FIN
$passwd
\dt
FIN

echo "Creating indices List"
psql -U sourceforge -h `hostname -s` sourceforge 2>&1 > desc/indices <<-FIN
$passwd
\di
FIN

echo "Creating sequences List"
psql -U sourceforge -h `hostname -s` sourceforge 2>&1 > desc/sequences <<-FIN
$passwd
\ds
FIN

echo "Creating views List"
psql -U sourceforge -h `hostname -s` sourceforge 2>&1 > desc/views <<-FIN
$passwd
\dv
FIN

