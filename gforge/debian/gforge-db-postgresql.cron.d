#
# Regular cron jobs for the gforge-db-postgresql package
#

# Recalculate user popularity metric
25 1 * * * gforge [ -x /usr/lib/gforge/bin/calculate_user_metric.php ] && /usr/lib/gforge/bin/calculate_user_metric.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily recalculate of the sums under the trove map
30 1 * * * gforge [ -x /usr/lib/gforge/bin/db_trove_maint.php ] && /usr/lib/gforge/bin/db_trove_maint.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily deletion of sessions, closing jobs, etc
35 1 * * * gforge [ -x /usr/lib/gforge/bin/project_cleanup.php ] && /usr/lib/gforge/bin/project_cleanup.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily crunching of survey data and other associated ratings
40 1 * * * gforge [ -x /usr/lib/gforge/bin/rating_stats.php ] && /usr/lib/gforge/bin/rating_stats.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily crunching of project summary data (counts)
42 1 * * * gforge [ -x /usr/lib/gforge/bin/db_project_sums.php ] && /usr/lib/gforge/bin/db_project_sums.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily close pending artifacts
43 1 * * * gforge [ -x /usr/lib/gforge/bin/check_stale_tracker_items.php ] && /usr/lib/gforge/bin/check_stale_tracker_items.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Weekly project metrics
50 1 * * Mon gforge [ -x /usr/lib/gforge/bin/project_weekly_metric.php ] && /usr/lib/gforge/bin/project_weekly_metric.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily rotation of the activity_log
0 0 * * * gforge [ -x /usr/lib/gforge/bin/rotate_activity.php ] && /usr/lib/gforge/bin/rotate_activity.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily aggregating of the numbers
15 0 * * * gforge [ -x /usr/lib/gforge/bin/site_stats.php ] && /usr/lib/gforge/bin/site_stats.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily sweep of the stats into final tables
45 0 * * * gforge [ -x /usr/lib/gforge/bin/db_stats_agg.php ] && /usr/lib/gforge/bin/db_stats_agg.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Hourly sending of mass e-mailings
48 * * * * gforge [ -x /usr/lib/gforge/bin/massmail.php ] && /usr/lib/gforge/bin/massmail.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Weekly db vacuum
50 2 * * Mon gforge [ -x /usr/lib/gforge/bin/vacuum.php ] && /usr/lib/gforge/bin/vacuum.php -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily sweep of the HTTP log files for project activity
15 0 * * * gforge [ -x /usr/lib/gforge/bin/stats_projects_logparse.pl ] && /usr/lib/gforge/bin/stats_projects_logparse.pl -d include_path=/usr/share/gforge/:/usr/share/gforge/www/include > /dev/null 2>&1

# Daily mail for not approved news
30 17 * * * root [ -x /usr/lib/gforge/bin/get_news_notapproved.pl ] && /usr/lib/gforge/bin/get_news_notapproved.pl > /dev/null 2>&1
