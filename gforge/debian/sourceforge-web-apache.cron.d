#
# Regular cron jobs for the sourceforge-web-apache package
#

# Clean cached files older than 60 minutes
25 * * * * root [ -d /var/cache/sourceforge ] && find /var/cache/sourceforge/ -type f -and -cmin +60 -exec /bin/rm -f "{}" \; > /dev/null 2>&1
