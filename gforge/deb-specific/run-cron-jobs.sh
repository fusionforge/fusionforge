#! /bin/bash
# Trigger a run of all cron jobs for the sourceforge package
# Script to be run by root

# Clean cached files older than 60 minutes
find /var/cache/sourceforge/ -type f -and -cmin +60 -exec /bin/rm -f "{}" \;

# Grab projects from trove map and put into foundry_projects table
su -c "/usr/lib/sourceforge/bin/populate_foundries.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Recalculate user popularity metric
su -c "/usr/lib/sourceforge/bin/calculate_user_metric.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Daily recalculate of the sums under the trove map
su -c "/usr/lib/sourceforge/bin/db_trove_maint.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Daily deletion of sessions, closing jobs, etc
su -c "/usr/lib/sourceforge/bin/project_cleanup.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Daily crunching of survey data and other associated ratings
su -c "/usr/lib/sourceforge/bin/rating_stats.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Daily project metrics
su -c "/usr/lib/sourceforge/bin/project_metric.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Weekly project metrics
su -c "/usr/lib/sourceforge/bin/project_weekly_metric.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Database vacuuming
# Theoretically unneeded: postgres does it at 4:00 by default
# 0 2 * * * sourceforge [ -x /usr/lib/sourceforge/bin/vacuum.php ] && /usr/lib/sourceforge/bin/vacuum.php -d include_path=/usr/lib/sourceforge/www/include 2>&1 > /dev/null

# Daily rotation of the activity_log
su -c "/usr/lib/sourceforge/bin/rotate_activity.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# Daily aggregating of the numbers
su -c "/usr/lib/sourceforge/bin/site_stats.php -d include_path=/usr/lib/sourceforge/www/include" - sourceforge

# DNS Update
su -c "/usr/lib/sourceforge/bin/dns_conf.pl && /usr/sbin/invoke-rc.d bind9 reload" - sourceforge

# Mailing-list creation
/usr/lib/sourceforge/bin/create-mailing-lists.pl

# FTP update
/usr/lib/sourceforge/bin/install-ftp.sh update

# CVS tarballs
/usr/lib/sourceforge/bin/tarballs.sh

# CVS/user/group update
/usr/lib/sourceforge/bin/update-user-group-cvs.sh
