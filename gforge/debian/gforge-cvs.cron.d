#
# Regular cron jobs for the gforge-cvs package
#

# CVS tarballs
5 2 * * * root [ -x /usr/lib/gforge/bin/tarballs.sh ] && /usr/lib/gforge/bin/tarballs.sh generate > /dev/null 2>&1

# CVS statistics
5 1 * * * root [ -x /usr/lib/gforge/bin/stats_cvs.pl ] && /usr/lib/gforge/bin/stats_cvs.pl > /dev/null 2>&1
