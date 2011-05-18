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
$cron_arr = array();
$cron_arr[0]='unused';
$cron_arr[1]='calculate_user_metric.php';
$cron_arr[2]='check_stale_tracker_items.php';
$cron_arr[3]='db_project_sums.php';
$cron_arr[4]='db_stats_agg.php';
$cron_arr[5]='db_trove_maint.php';
$cron_arr[6]='massmail.php';
$cron_arr[7]='project_cleanup.php';
$cron_arr[8]='project_weekly_metric.php';
$cron_arr[9]='rating_stats.php';
$cron_arr[10]='rotate_activity.php';
$cron_arr[11]='site_stats.php';
$cron_arr[12]='vacuum.php';
$cron_arr[13]='cvs.php';
$cron_arr[14]='history_parse.php';
$cron_arr[15]='ssh_create.php';
$cron_arr[16]='usergroup.php';
$cron_arr[17]='mailaliases.php';
$cron_arr[18]='mailing_lists_create.php';
$cron_arr[19]='tarballs.php';
$cron_arr[20]='reporting_cron.php';
$cron_arr[21]='create_svn.php';
$cron_arr[22]='daily_task_email.php';
$cron_arr[23]='backup_site.php';
$cron_arr[24]='svn-stats.php';
$cron_arr[25]='homedirs.php';
$cron_arr[26]='update_users.php';
$cron_arr[27]='create_scm_repos.php';
$cron_arr[28]='gather_scm_stats.php';

$cron_arr[901]='create_groups.php';
$cron_arr[902]='mailing_lists_index.php';

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

//
//  Create lock via semaphore so long running jobs don't overlap
//
//  Parameters
//  $name - Name of cron job to use in the lock file name
//
function cron_create_lock($name) {
        global $cron_utils_sem ;
        if (! $cron_utils_sem[$name]) {
                $token = ftok ($name, 'g');
                $cron_utils_sem[$name] = sem_get ($token, 1, 0600, 0) ;
        }
        return sem_acquire ($cron_utils_sem[$name]);
}

//
//  Delete lock created by cron_create_lock
//
//  Parameters
//  $name - Name of cron job to use in the lock file name
//
function cron_remove_lock($name) {
        global $cron_utils_sem ;
        if (! $cron_utils_sem[$name]) {
                $token = ftok ($name, 'g');
                $cron_utils_sem[$name] = sem_get ($token, 1, 0600, 0) ;
        }
	return sem_release ($cron_utils_sem[$name]);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
