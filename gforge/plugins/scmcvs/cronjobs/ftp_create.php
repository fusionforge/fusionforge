#! /usr/bin/php4 -f
<?php
/**
 * GForge Cron Job
 *
 * The rest Copyright 2002-2005 (c) GForge Team
 * http://gforge.org/
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
	This file creates the root directories for the FTP
*/
require_once('squal_pre.php');
require ('common/include/cron_utils.php');

$err = '';

if ($sys_use_ftpuploads) { 	
	//
	//	Add the groups from the gforge database
	//
	$res=db_query("SELECT group_id,unix_group_name FROM groups WHERE status='A' AND type_id='1'");
	for($i = 0; $i < db_numrows($res); $i++) {
	    $groups[] = db_result($res,$i,'unix_group_name');
	}
	
	//
	//	Create home dir for groups
	//
	foreach($groups as $group) {
	
		//create an FTP upload dir for this project
		$destdir = $sys_ftp_upload_dir.'/'.$group;
		if (util_is_root_dir($destdir)) {
			$err .= "Trying to access root directory\n";
			continue;
		}

		if (!is_dir($destdir)) {
			if (!@mkdir($destdir)) {
				$err .= 'Could not create dir: '.$destdir."\n";
				continue;
			} 
			// Unfortunately some versions of PHP don't set the mode properly when 
			// specified on the mkdir command, so we need to do it here
			if (!@chmod($destdir, 0770)) {
				$err .= 'Could not chmod dir: '.$destdir."\n";
				continue;
			} 
			if (!@chown($destdir, $sys_apache_user)) {
				$err .= 'Could not chown dir: '.$destdir."\n";
				continue;
			} 
			if (!@chgrp($destdir, $sys_apache_group)) {
				$err .= 'Could not chgrp dir: '.$destdir."\n";
				continue;
			} 
		}
	}
}
?>
