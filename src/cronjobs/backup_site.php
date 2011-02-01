#! /usr/bin/php
<?php
/**
 * Backup SITE job
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require dirname(__FILE__).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require $gfcommon.'include/cron_utils.php';


$database=forge_get_config('database_name');
$username=forge_get_config('database_user');
$password=forge_get_config('database_password');
$host=forge_get_config('database_host');
$port=forge_get_config('database_port');

$datetime=date('Y-m-d'); //we will use this to concatenate it with the tar filename

if(!(isset($sys_path_to_backup)) ||  (strcmp($sys_path_to_backup,"/") == 0)){
//	cron_entry(23,'Variable $sys_path_to_backup was not set or it was equal to /.');	
//	exit;
	// Default value
	$sys_path_to_backup = '/gforge-backups/';
}

if(util_is_root_dir($sys_path_to_backup)){
	$sys_path_to_backup=$sys_path_to_backup.'/';
}

if (!is_dir($sys_path_to_backup)) {
	// try to recursively create it
	$subdirs = explode('/', $sys_path_to_backup);
	$path = '';
	foreach ($subdirs as $subdir) {
		$subdir = trim($subdir);
		if (empty($subdir)) continue;
		$path .= '/'.$subdir;
		if (!file_exists($path)) {
			if (!mkdir($path)) {
				cron_entry(23,'Couldn\'t create directory '.$path.' for backups');	
				exit;
			}
		}
	}
}

// add trailing slash
if (!preg_match('/\\/$/',$sys_path_to_backup)) {
	$sys_path_to_backup .= '/';
}

$output = "";
$err = "";
$dump_cmd = 'pg_dump -U ' . $username;
if ($host) {
	$dump_cmd .= ' -h ' . $host;
}
if ($port) {
	$dump_cmd .= ' -p ' . $port;
}

$tmpfname = tempnam(sys_get_temp_dir(), "tmp");

$handle = fopen($tmpfname, "w");
$line = '';
$line .= $host ? "$host:" : "localhost:";
$line .= $port ? "$port:" : "5432:";
$line .= "$database:$username:$password";
fwrite($handle, "$line");
fclose($handle);

$dump_cmd .= ' -v -Ft -b '.$database;
@exec('PGPASSFILE='.$tmpfname.' '.$dump_cmd.' 2>&1 > '.$sys_path_to_backup.'db-'.$database.'-tmp-'.$datetime.'.tar ',$output,$retval);   //proceed with db dump
unlink($tmpfname);

if($retval!=0){
	$err.= implode("\n", $output);
}

/**************************************
 * Backup uploads dir
 **************************************/ 
$output="";
if (file_exists(forge_get_config('upload_dir'))) {
	@exec('tar -hjcvf '.$sys_path_to_backup.'uploads-tmp-'.$datetime.'.tar.bz2 '.forge_get_config('upload_dir').' 2>&1' ,$output,$retval);   //proceed upload dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);
	}
} else {
		$err.= 'Unable to find upload dir. Configured value is:'.forge_get_config('upload_dir');
}

/**************************************
 * Backup mailing lists files
 **************************************/ 
$output="";
// Most probable mailman data dir
$mailman_data_dir = '/var/lib/mailman';
if (file_exists($mailman_data_dir)) {
	@exec('tar -jcvf '.$sys_path_to_backup.'mailinglist-tmp-'.$datetime.'.tar.bz2 '.$mailman_data_dir.'/ 2>&1', $output,$retval);   //proceed mailman dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);	
	} 
} else {
		$err.= 'Unable to find Mailman data dir. Please edit backup_site.php cronjob';
}

/**************************************
 * Backup CVS repositories
 **************************************/ 
$output="";
if (file_exists(forge_get_config('repos_path', 'scmcvs'))) {
	@exec('tar -hjcvf '.$sys_path_to_backup.'cvsroot-tmp-'.$datetime.'.tar.bz2 '.forge_get_config('repos_path', 'scmcvs').'/ 2>&1' ,$output,$retval);   //proceed cvsroot dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);
	}
} else {
	$err.= 'Unable to find CVSROOT dir. Configured value is:'.forge_get_config('repos_path', 'scmcvs');
}

/**************************************
 * Backup SVN repositories (using the hot-backup.py script)
 **************************************/ 
if (file_exists($svndir_prefix)) {
	$hot_backup = dirname(__FILE__).'/hot-backup.py';
	$svn_path = dirname(`which svn`);
	$output="";	
	
	if (!file_exists($hot_backup) || !$svn_path) {
		// hot-backup.py script not available, try regular backup (doesn't check for inconsistencies)
		@exec('tar -hjcvf '.$sys_path_to_backup.'svnroot-tmp-'.$datetime.'.tar.bz2 '.$svndir_prefix.'/ 2>&1' ,$output,$retval);   //proceed svnroot dir tar file creation
		if($retval!=0){
			$err.= implode("\n", $output);
		}
	} else {
		// backup the files using hot-backup script
		$repos_backup_dir = $sys_path_to_backup.'/svn-repositories-'.$datetime.'/';
		mkdir($repos_backup_dir);
		$dh = opendir($svndir_prefix);
		while ($file = readdir($dh)) {
			if (preg_match('/^\\./', $file)) continue;		// skip files that start with a dot
			$path = $svndir_prefix.'/'.$file;
			if (!is_dir($path)) continue;		// not a repository
			$cmd = 'SVN_PATH="'.$svn_path.'" '.$hot_backup.' '.$path.' '.$repos_backup_dir;
			@exec($cmd, $output, $retval);
			if($retval!=0){
				$err.= implode("\n", $output);
			}

		}
		
		@exec('tar -hjcvf '.$sys_path_to_backup.'svnroot-tmp-'.$datetime.'.tar.bz2 '.$repos_backup_dir.' 2>&1' ,$output,$retval);
		if($retval!=0){
			$err.= implode("\n", $output);
		}
		
		@exec("rm -rf ".$repos_backup_dir);
	}
} else {
	$err.= 'Unable to find SVNROOT dir. Configured value is:'.$svndir_prefix;
}

/**************************************
 * Backup config files
 **************************************/ 
$output="";
if (file_exists(forge_get_config('config_path'))) {
	@exec('tar -jcvf '.$sys_path_to_backup.'etc-tmp-'.$datetime.'.tar.bz2 '.forge_get_config('config_path').' 2>&1' ,$output,$retval);   //proceed svnroot dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);
	}
} else {
	$err.= 'Unable to find '.forge_get_config('config_path').' dir.';
}


/**************************************
 * Create backup file
 **************************************/ 
$output="";
@exec('tar -jcvf '.$sys_path_to_backup.'backup'.$datetime.'.tar.bz2 '.$sys_path_to_backup.'*-tmp-'.$datetime.'*  2>&1',$output,$retval);
if($retval!=0){
	$err.= implode("\n", $output);
}

//If execution of tar command was successfull ($retval equals zero) remove individual files
if($retval==0){	
	$output="";
	@exec('rm '.$sys_path_to_backup.'*tmp-'.$datetime.'*  2>&1',$output,$retval);
	if($retval!=0){
		$err.= implode("\n", $output);
	}
}


cron_entry(23,$err);

?>
