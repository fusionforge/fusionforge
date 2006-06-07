#
# Regular cron jobs for the gforge-plugin-scmcvs package
#

# CVS tarballs
5 2 * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/tarballs.sh ] && /usr/lib/gforge/plugins/scmcvs/bin/tarballs.sh generate
5 * * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump_update.pl ] && /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump_update.pl