#
# Regular cron jobs for the gforge-ftp-proftpd package
#

# FTP update
0 * * * * root [ -x /usr/lib/gforge/bin/install-ftp.sh ] && /usr/lib/gforge/bin/install-ftp.sh update > /dev/null 2>&1
