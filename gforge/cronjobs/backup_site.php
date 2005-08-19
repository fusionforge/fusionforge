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
require ('common/include/cron_utils.php');

$database=$sys_dbname; //Database name from local.inc
$time=time();
$year=date('Y',$time); //obtain current year
$month=date('m',$time); //obtain current month
$day=date('d',$time); //obtain current day
$datetime=$year.'-'.$month.'-'.$day; //we will use this to concatenate it with the tar filename



system('pg_dump -Ft -b '.$database.' > '.$sys_path_to_backup.'db-'.$database.'-tmp-'.$datetime.'.tar', $retval);   //proceed with db dump
system('tar -cvf '.$sys_path_to_backup.'uploads-tmp-'.$datetime.'.tar '.$sys_upload_dir , $retval);   //proceed upload dir tar file creation
system('tar -cvf '.$sys_path_to_backup.'mailinglist-tmp-'.$datetime.'.tar '.$sys_path_mailing, $retval);   //proceed mailman dir tar file creation
system('tar -cvf '.$sys_path_to_backup.'cvsroot-tmp-'.$datetime.'.tar '.$sys_path_cvsroot , $retval);   //proceed cvsroot dir tar file creation
system('tar -cvf '.$sys_path_to_backup.'svnroot-tmp-'.$datetime.'.tar '.$sys_path_svnroot , $retval);   //proceed svnroot dir tar file creation

//Now we store all the tar files we've just created in one tar called backup(date).tar 
system('tar -cvf '.$sys_path_to_backup.'backup'.$datetime.'.tar '.$sys_path_to_backup.'*'.$datetime.'*',$retval);

//If execution of tar command was successfull ($retval equals zero) remove individual files
if($retval==0){	
	system('rm '.$sys_path_to_backup.'*tmp-'.$datetime.'*',$retval);
}


/*

./home/tperdue/share/gforge-gfg/cvsroot
./home/tperdue/share/gforge-4.5.0.1-gfg/cvsroot

*/
?>
