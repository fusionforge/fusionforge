#
# Regular cron jobs for the gforge-ftp-proftpd package
#

# FTP update
0 * * * * root [ -x /usr/lib/gforge/bin/install-ftp.sh ] && /usr/lib/gforge/bin/install-ftp.sh update > /dev/null 2>&1

# create and mount project directory in user's home directory
# added by fabio bertagnin nov 2005
0 * * * * root [ -x /usr/lib/gforge/bin/ftp_create_group_access.php ] && /usr/bin/php5 -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/lib/gforge/bin/ftp_create_group_access.php > /dev/null 2>&1