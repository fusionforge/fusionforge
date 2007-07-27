#
# Regular cron jobs for the gforge-plugins scmsvn
#

# Tarballs
5 2 * * * root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/tarballs.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/share/gforge/plugins/scmsvn/cronjobs/tarballs.php > /dev/null 2>&1

# Snapshots
35 3 * * * root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/snapshots.sh ] && /usr/share/gforge/plugins/scmsvn/cronjobs/snapshots.sh generate > /dev/null 2>&1

# Repositories update
45 * * * * root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/svn_dump.pl ] && su -s /bin/sh gforge -c /usr/share/gforge/plugins/scmsvn/cronjobs/svn_dump.pl > /dev/null 2>&1 && [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/svn_update.pl ] && /usr/share/gforge/plugins/scmsvn/cronjobs/svn_update.pl > /dev/null 2>&1

# Statistics
55 4 * * Sun root [ -x /usr/share/gforge/plugins/scmsvn/cronjobs/svn-stats.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/share/gforge/plugins/scmsvn/cronjobs/svn-stats.php > /dev/null 2>&1
