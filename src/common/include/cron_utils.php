<?php
/**
 * FusionForge cron job utilities
 *
 * Copyright 2003, GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

//
//	This key id# is important - do not change or renumber
//
require_once $gfcommon.'include/SysTasksQ.class.php';
$cron_arr = array();
$cron_arr[0]='unused';
$cron_arr[1]='db/calculate_user_metric.php';
$cron_arr[2]='db/check_stale_tracker_items.php';
$cron_arr[3]='db/db_project_sums.php';
$cron_arr[4]='db/db_stats_agg.php';
$cron_arr[5]='db/db_trove_maint.php';
$cron_arr[6]='db/massmail.php';
$cron_arr[7]='db/project_cleanup.php';
$cron_arr[8]='db/project_weekly_metric.php';
$cron_arr[9]='db/rating_stats.php';
$cron_arr[10]='db/rotate_activity.php';
$cron_arr[11]='db/site_stats.php';
$cron_arr[12]='db/vacuum.php';
#$cron_arr[13]='cvs.php';
#$cron_arr[14]='history_parse.php';
#$cron_arr[15]='ssh_create.php';
#$cron_arr[16]='usergroup.php';
#$cron_arr[17]='misc/mailaliases.php';
$cron_arr[18]='lists/mailing_lists_create.php';
#$cron_arr[19]='tarballs.php';
$cron_arr[20]='db/reporting_cron.php';
#$cron_arr[21]='create_svn.php';
$cron_arr[22]='db/daily_task_email.php';
#$cron_arr[23]='misc/backup_site.php';
#$cron_arr[24]='svn-stats.php';
$cron_arr[SYSTASK_HOMEDIR]='shell/homedirs.php';
#$cron_arr[26]='update_users.php';
$cron_arr[SYSTASK_SCM_REPO]='scm/create_scm_repos.php';
$cron_arr[28]='scm/gather_scm_stats.php';
#$cron_arr[29]='weekly.php';
$cron_arr[30]='web-vhosts/create_vhosts.php';

#$cron_arr[901]='create_groups.php';
#$cron_arr[902]='mailing_lists_index.php';
#$cron_arr[903]='job-server.pl';

function cron_entry($job,$output) {
	$sql='INSERT INTO cron_history (rundate,job,output)
		values ($1, $2, $3)' ;
	return db_query_params ($sql,
				array (time(), $job, $output));
}

function cron_debug($string) {
	global $verbose;
	if($verbose) {
		echo $string."\n";
	}
}

function checkChroot() {

	if(forge_get_config('chroot') != '' && is_dir(forge_get_config('chroot'))) {
		return true;
	}
	return false;
}

// Locking: for a single script
// flock() locks are automatically lost on program termination, however
// that happened (clean, segfault...)
function cron_acquire_lock($script) {
	// Script lock: http://perl.plover.com/yak/flock/samples/slide006.html
	static $lock;  // static, otherwise auto-closed by PHP and we lose the lock!
	$lock = fopen($script, 'r') or die("Failed to ask lock.\n");

	if (!flock($lock, LOCK_EX | LOCK_NB)) {
		die("There's a lock for '$script', exiting\n");
	}
}

//
// Reload NSCD, in particular when replicating new groups, users or
// project memberships
// 
function cron_reload_nscd() {
        system("(nscd -i passwd && nscd -i group) >/dev/null 2>&1");
}

function cron_reload_apache() {
        system("service apache2 reload || service httpd reload >/dev/null 2>&1");
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
