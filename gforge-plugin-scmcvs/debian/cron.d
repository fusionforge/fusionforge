#
# Regular cron jobs for the gforge-plugin-scmcvs package
#

# CVS tarballs
5 2 * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/tarballs.sh ] && /usr/lib/gforge/plugins/scmcvs/bin/tarballs.sh generate
5 * * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump.pl ] && su -s /bin/sh gforge -c /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump.pl && [ -x /usr/lib/gforge/plugins/scmcvs/bin/cvs_update.pl ] && /usr/lib/gforge/plugins/scmcvs/bin/cvs_update.pl
