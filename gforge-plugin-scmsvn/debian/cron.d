#
# Regular cron jobs for the gforge-plugin-scmsvn package
#

# SVN tarballs
# 5 2 * * * root [ -x /usr/lib/gforge/plugins/scmsvn/bin/tarballs.sh ] && /usr/lib/gforge/plugins/scmsvn/bin/tarballs.sh generate
5 * * * * root [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn_dump_update.pl ] && /usr/lib/gforge/plugins/scmsvn/bin/svn_dump_update.pl
45 4 * * Sun root [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl ] && /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl
