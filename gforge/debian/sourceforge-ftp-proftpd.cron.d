#
# Regular cron jobs for the sourceforge-ftp-proftpd package
#

# FTP update
0 * * * * root [ -x /usr/lib/sourceforge/bin/install-ftp.sh ] && /usr/lib/sourceforge/bin/install-ftp.sh update 2>&1 > /dev/null
