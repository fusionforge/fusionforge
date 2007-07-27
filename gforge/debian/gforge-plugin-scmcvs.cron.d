#
# Regular cron jobs for gforge-plugin-scmcvs
#
# Tarballs
5 2 * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/tarballs.php ] && php -d include_path=/etc/gforge:/usr/share/gforge/:/usr/share/gforge/www/include /usr/share/gforge/plugins/scmcvs/cronjobs/tarballs.php > /dev/null 2>&1

# Snapshots
35 2 * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/snapshots.sh ] && /usr/share/gforge/plugins/scmcvs/cronjobs/snapshots.sh generate > /dev/null 2>&1

# Repositories update (.pl in debian)
5 * * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_dump.pl ] && su -s /bin/sh gforge -c /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_dump.pl > /dev/null 2>&1 && [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_update.pl ] && /usr/share/gforge/plugins/scmcvs/cronjobs/cvs_update.pl > /dev/null 2>&1

# CVS add/commit Statistics
45 4 * * * root [ -x /usr/share/gforge/plugins/scmcvs/cronjobs/cvs-stats.pl ] && /usr/share/gforge/plugins/scmcvs/cronjobs/cvs-stats.pl > /dev/null 2>&1
