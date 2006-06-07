#
# Regular cron jobs for the gforge-plugin-scmcvs package
#

# Tarballs
5 2 * * * root [ -x /usr/lib/gforge/plugins/scmcvs/cronjobs/tarballs.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/lib/gforge/plugins/scmcvs/cronjobs/tarballs.php

# Snapshots
35 2 * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/snapshots.sh ] && /usr/lib/gforge/plugins/scmcvs/bin/snapshots.sh generate

# Repositories update
5 * * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump.pl ] && su -s /bin/sh gforge -c /usr/lib/gforge/plugins/scmcvs/bin/cvs_dump.pl && [ -x /usr/lib/gforge/plugins/scmcvs/bin/cvs_update.pl ] && /usr/lib/gforge/plugins/scmcvs/bin/cvs_update.pl

# CVS add/commit Statistics
45 4 * * * root [ -x /usr/lib/gforge/plugins/scmcvs/bin/stats_cvs.pl ] && /usr/lib/gforge/plugins/scmcvs/bin/stats_cvs.pl
