#
# Regular cron jobs for the gforge-cvs package
#

# CVS tarballs
5 2 * * * root [ -x /usr/lib/gforge/bin/tarballs.sh ] && /usr/lib/gforge/bin/tarballs.sh generate > /dev/null 2>&1
