#
# Regular cron jobs for the gforge-plugins scmcvs
#
# Tarballs
5 2 * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/tarballs.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/share/gforge/plugins/scmcvs/cronjobs/tarballs.php > /dev/null 2>&1

# Snapshots
35 2 * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/snapshots.sh ] && /usr/share/gforge/plugins/scmcvs/cronjobs/snapshots.sh generate

# Repositories update (.pl in debian)
5 * * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_dump.pl ] && su -s /bin/sh gforge -c /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_dump.pl && [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_update.pl ] && /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_update.pl

# CVS add/commit Statistics
45 4 * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/cvs-stats.pl ] && /usr/share/gforge/plugins/scmcvs/cronjobs/cvs-stats.pl > /dev/null 2>&1

#
# Regular cron jobs for the gforge-plugins scmsvn
#

# Tarballs
5 2 * * * root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/tarballs.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/share/gforge/plugins/scmsvn/cronjobs/tarballs.php

# Snapshots
35 3 * * * root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/snapshots.sh ] && /usr/share/gforge/plugins/scmsvn/cronjobs/snapshots.sh generate

# Repositories update
45 * * * * root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/svn_dump.pl ] && su -s /bin/sh gforge -c /usr/share/gforge/plugins/scmsvn/cronjobs/svn_dump.pl && [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/svn_update.pl ] && /usr/share/gforge/plugins/scmsvn/cronjobs/svn_update.pl

# Statistics
55 4 * * Sun root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/svn-stats.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/share/gforge/plugins/scmsvn/cronjobs/svn-stats.php
