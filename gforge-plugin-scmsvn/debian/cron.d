#
# Regular cron jobs for the gforge-plugin-scmsvn package
#

# Tarballs
5 2 * * * root [ -x /usr/lib/gforge/plugins/scmsvn/cronjobs/tarballs.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/lib/gforge/plugins/scmsvn/cronjobs/tarballs.php

# Snapshots
35 3 * * * root [ -x /usr/lib/gforge/plugins/scmsvn/bin/snapshots.sh ] && /usr/lib/gforge/plugins/scmsvn/bin/snapshots.sh generate

# Repositories update
45 * * * * root [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn_dump.pl ] && su -s /bin/sh gforge -c /usr/lib/gforge/plugins/scmsvn/bin/svn_dump.pl && [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn_update.pl ] && /usr/lib/gforge/plugins/scmsvn/bin/svn_update.pl

# Statistics
55 4 * * Sun root [ -x /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl ] && /usr/lib/gforge/plugins/scmsvn/bin/svn-stats.pl
