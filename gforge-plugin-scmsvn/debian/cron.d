#
# Regular cron jobs for the gforge-plugin-scmsvn package
#

# Tarballs
5 3 * * * root [ -x /usr/lib/gforge/plugins/scmsvn/bin/tarballs.sh ] && /usr/lib/gforge/plugins/scmsvn/bin/tarballs.sh generate

# Snapshots
35 3 * * * root [ -x /usr/lib/gforge/plugins/scmsvn/bin/snapshots.sh ] && /usr/lib/gforge/plugins/scmsvn/bin/snapshots.sh generate

# Repositories update
45 * * * * root [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn_dump_update.pl ] && /usr/lib/gforge/plugins/scmsvn/bin/svn_dump_update.pl

# Statistics
55 4 * * Sun root [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl ] && /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl
