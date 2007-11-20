<?php

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

function cron_entry($job,$output) {
	$sql="INSERT INTO cron_history (rundate,job,output) 
		values ('".time()."','$job','".addslashes($output)."')";
	return db_query($sql);
}

function cron_debug($string) {
	global $verbose;
	if($verbose) {
		echo $string."\n";
	}
}

function checkChroot() {
	global $sys_chroot;
	if(isset($sys_chroot) && !empty($sys_chroot) && is_dir($sys_chroot)) {
		return true;
	}
	return false;
}

function chrootPath($path) {
	global $sys_chroot;
	if(checkChroot()) {
		$path = $sys_chroot.$path;
	}
	return $path;
}

function chrootCommand($command) {
	global $sys_chroot;
	if(checkChroot()) {
		$command = 'chroot '.$sys_chroot.' '.$command;
	}
	return $command;
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
