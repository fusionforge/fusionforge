#
# Regular cron jobs for the gforge-web-apache package
#

# Clean cached files older than 60 minutes
25 * * * * root [ -d /var/cache/gforge ] && find /var/cache/gforge/ -type f -and -cmin +60 -exec /bin/rm -f "{}" \; > /dev/null 2>&1
