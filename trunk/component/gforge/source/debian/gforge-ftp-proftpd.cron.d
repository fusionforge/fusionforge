#
# Regular cron jobs for the gforge-ftp-proftpd package
#

INCLUDE_PATH=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www:/usr/share/gforge/common

# FTP update
0 * * * * root [ -x /usr/lib/gforge/bin/install-ftp.sh ] && /usr/lib/gforge/bin/install-ftp.sh update > /dev/null 2>&1

# create and mount project directory in user's home directory
0 * * * * root [ -x /usr/lib/gforge/bin/ftp_create_group_access.php ] && /usr/bin/php5 -d include_path=$INCLUDE_PATH /usr/lib/gforge/bin/ftp_create_group_access.php > /dev/null 2>&1