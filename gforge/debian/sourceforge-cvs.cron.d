#
# Regular cron jobs for the sourceforge-cvs package
#

# CVS tarballs
5 2 * * * root [ -x /usr/lib/sourceforge/bin/tarballs.sh ] && /usr/lib/sourceforge/bin/tarballs.sh generate 2>&1 > /dev/null
