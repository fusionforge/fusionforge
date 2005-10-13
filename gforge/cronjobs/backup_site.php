#! /usr/bin/php4 -f
<?php
/**
 * Backup SITE job
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once('local.inc');
require ('squal_pre.php');
require ('common/include/cron_utils.php');


$database=$sys_dbname; //Database name from local.inc
$username=$sys_dbuser; //Username used to log on to data base
$password=$sys_dbpasswd; //Db Password 

$datetime=date('Y-m-d'); //we will use this to concatenate it with the tar filename

if(!(isset($sys_path_to_backup)) ||  (strcmp($sys_path_to_backup,"/") == 0)){
	cron_entry(23,'Variable $sys_path_to_backup was not set or it was equal to /.');	
	exit;
}

if(util_is_root_dir($sys_path_to_backup)){
	$sys_path_to_backup=$sys_path_to_backup.'/';
}


$output = "";
$err = "";
@exec('echo -n -e "'.$password.'\n" | pg_dump -U '.$username.' -v -Ft -b 2>&1 '.$database.' > '.$sys_path_to_backup.'db-'.$database.'-tmp-'.$datetime.'.tar ',$output,$retval);   //proceed with db dump
if($retval!=0){
	$err.= implode("\n", $output);
}

$output="";
if (file_exists($sys_upload_dir)) {
	@exec('tar -cvf '.$sys_path_to_backup.'uploads-tmp-'.$datetime.'.tar '.$sys_upload_dir.' 2>&1' ,$output,$retval);   //proceed upload dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);
	}
} else {
		$err.= 'Unable to find Upload Dir. Value on local.inc is:'.$sys_upload_dir;
}

$output="";
if (file_exists($sys_path_to_mailman)) {
	@exec('tar -cvf '.$sys_path_to_backup.'mailinglist-tmp-'.$datetime.'.tar '.$sys_path_to_mailman.'/ 2>&1', $output,$retval);   //proceed mailman dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);	
	} 
} else {
		$err.= 'Unable to find Mailman Dir. Value on local.inc is:'.$sys_path_to_mailman;
}

$output="";
if (file_exists($cvsdir_prefix)) {
	@exec('tar -cvf '.$sys_path_to_backup.'cvsroot-tmp-'.$datetime.'.tar '.$cvsdir_prefix.'/ 2>&1' ,$output,$retval);   //proceed cvsroot dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);
	}
} else {
	$err.= 'Unable to find CVSROOT Dir. Value on local.inc is:'.$cvsdir_prefix;
}
$output="";
if (file_exists($svndir_prefix)) {
	@exec('tar -cvf '.$sys_path_to_backup.'svnroot-tmp-'.$datetime.'.tar '.$svndir_prefix.'/ 2>&1' ,$output,$retval);   //proceed svnroot dir tar file creation
	if($retval!=0){
		$err.= implode("\n", $output);
	}
} else {
	$err.= 'Unable to find SVNROOT Dir. Value on local.inc is:'.$svndir_prefix;
}

//Now we store all the tar files we've just created in one tar called backup(date).tar 
$output="";
@exec('tar -cvf '.$sys_path_to_backup.'backup'.$datetime.'.tar '.$sys_path_to_backup.'*-tmp-'.$datetime.'*  2>&1',$output,$retval);
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


cron_entry(23,addslashes($err));

?>
