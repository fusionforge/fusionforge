#
# Regular cron jobs for the sourceforge package
#

web_only=true

# Clean cached files older than 60 minutes
25 * * * * root [ -d /var/cache/sourceforge ] && find /var/cache/sourceforge/ -type f -and -cmin +60 -exec /bin/rm -f "{}" \; &> /dev/null

# Grab projects from trove map and put into foundry_projects table
15 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/populate_foundries.php ] && /usr/lib/sourceforge/bin/populate_foundries.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Recalculate user popularity metric
25 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/calculate_user_metric.php ] && /usr/lib/sourceforge/bin/calculate_user_metric.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Daily recalculate of the sums under the trove map
30 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/db_trove_maint.php ] && /usr/lib/sourceforge/bin/db_trove_maint.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Daily deletion of sessions, closing jobs, etc
35 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/project_cleanup.php ] && /usr/lib/sourceforge/bin/project_cleanup.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Daily crunching of survey data and other associated ratings
40 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/rating_stats.php ] && /usr/lib/sourceforge/bin/rating_stats.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Daily project metrics
45 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/project_metric.php ] && /usr/lib/sourceforge/bin/project_metric.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Weekly project metrics
50 1 * * Mon sourceforge [ -x /usr/lib/sourceforge/bin/project_weekly_metric.php ] && /usr/lib/sourceforge/bin/project_weekly_metric.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Database vacuuming
# Theoretically unneeded: postgres does it at 4:00 by default
# 0 2 * * * sourceforge [ -x /usr/lib/sourceforge/bin/vacuum.php ] && /usr/lib/sourceforge/bin/vacuum.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Daily rotation of the activity_log
0 0 * * * sourceforge [ -x /usr/lib/sourceforge/bin/rotate_activity.php ] && /usr/lib/sourceforge/bin/rotate_activity.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# Daily aggregating of the numbers
15 0 * * * sourceforge [ -x /usr/lib/sourceforge/bin/site_stats.php ] && /usr/lib/sourceforge/bin/site_stats.php -d include_path=/usr/lib/sourceforge/www/include &> /dev/null

# DNS Update
0 * * * * sourceforge [ $web_only != "true" ] && [ -x /usr/lib/sourceforge/bin/dns_conf.pl ] && /usr/lib/sourceforge/bin/dns_conf.pl && /usr/sbin/rndc reload &>/dev/null

# CVS/user/group update
0 * * * * root [ $web_only != "true" ] && [ -x /usr/lib/sourceforge/bin/update-user-group-cvs.sh ] && /usr/lib/sourceforge/bin/update-user-group-cvs.sh
