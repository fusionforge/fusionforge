#
# Regular cron jobs for the gforge-plugin-scmcvs package
#

# Tarballs
5 2 * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/tarballs.sh ] && /usr/lib/gforge/plugins/scmcvs/bin/tarballs.sh generate

# Snapshots
35 2 * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/snapshots.sh ] && /usr/lib/gforge/plugins/scmcvs/bin/snapshots.sh generate

# Repositories update
5 * * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump.pl ] && su -s /bin/sh gforge -c /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump.pl && [ -x /usr/lib/gforge/plugins/scmcvs/bin/cvs_update.pl ] && /usr/lib/gforge/plugins/scmcvs/bin/cvs_update.pl

# Statistics
45 4 * * Sun root [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl ] && /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl
