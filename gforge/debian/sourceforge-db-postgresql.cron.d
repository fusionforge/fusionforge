#
# Regular cron jobs for the sourceforge-db-postgresql package
#

# Grab projects from trove map and put into foundry_projects table
15 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/populate_foundries.php ] && /usr/lib/sourceforge/bin/populate_foundries.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Recalculate user popularity metric
25 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/calculate_user_metric.php ] && /usr/lib/sourceforge/bin/calculate_user_metric.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily recalculate of the sums under the trove map
30 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/db_trove_maint.php ] && /usr/lib/sourceforge/bin/db_trove_maint.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily deletion of sessions, closing jobs, etc
35 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/project_cleanup.php ] && /usr/lib/sourceforge/bin/project_cleanup.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily crunching of survey data and other associated ratings
40 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/rating_stats.php ] && /usr/lib/sourceforge/bin/rating_stats.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily project metrics
#45 1 * * * sourceforge [ -x /usr/lib/sourceforge/bin/project_metric.php ] && /usr/lib/sourceforge/bin/project_metric.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Weekly project metrics
50 1 * * Mon sourceforge [ -x /usr/lib/sourceforge/bin/project_weekly_metric.php ] && /usr/lib/sourceforge/bin/project_weekly_metric.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Database vacuuming
# Theoretically unneeded: postgres does it at 4:00 by default
# 0 2 * * * sourceforge [ -x /usr/lib/sourceforge/bin/vacuum.php ] && /usr/lib/sourceforge/bin/vacuum.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily rotation of the activity_log
0 0 * * * sourceforge [ -x /usr/lib/sourceforge/bin/rotate_activity.php ] && /usr/lib/sourceforge/bin/rotate_activity.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily aggregating of the numbers
15 0 * * * sourceforge [ -x /usr/lib/sourceforge/bin/site_stats.php ] && /usr/lib/sourceforge/bin/site_stats.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily sweep of the HTTP log files for stats information
25 0 * * * sourceforge [ -x /usr/lib/sourceforge/bin/stats_logparse.sh ] && /usr/lib/sourceforge/bin/stats_logparse.sh > /dev/null 2>&1

# Daily sweep of the stats into final tables
45 0 * * * sourceforge [ -x /usr/lib/sourceforge/bin/db_stats_agg.php ] && /usr/lib/sourceforge/bin/db_stats_agg.php -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1

# Daily sweep of the HTTP log files for project activity
15 0 * * * sourceforge [ -x /usr/lib/sourceforge/bin/stats_projects_logparse.pl ] && /usr/lib/sourceforge/bin/stats_projects_logparse.pl -d include_path=/usr/lib/sourceforge/:/usr/lib/sourceforge/www/include > /dev/null 2>&1
