#! /usr/bin/php5 -f
<?php
/**
 * GForge Group Role Generator
 *
 * Copyright 2004 GForge, LLC
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require_once('www/include/squal_pre.php');
require_once('common/frs/FRSFile.class.php');
require_once('common/frs/FRSRelease.class.php');
require_once('common/frs/FRSPackage.class.php');
//
//  Set up this script to run as the site admin
//

$res = db_query("SELECT user_id FROM user_group WHERE admin_flags='A' AND group_id='1'");

if (!$res) {
	//echo db_error();
	exit(1);
}

if (db_numrows($res) == 0) {
	// There are no Admins yet, aborting without failing
	//echo "SUCCESS\n";
	exit(0);
}

$id=db_result($res,0,0);
session_set_new($id);

$res=db_query("SELECT group_id FROM groups WHERE status != 'P'");
$groups =& group_get_objects(util_result_column_to_array($res));

for ($g=0; $g<count($groups); $g++) {

//make group dirs
	$newdirlocation = $GLOBALS['sys_upload_dir'].'/'.$groups[$g]->getUnixName();
	$cmd="/bin/mkdir $newdirlocation";
	//echo "\n$cmd";
	if (!is_dir($newdirlocation)){
		exec($cmd,$out);
	}

	$frsps =& get_frs_packages($groups[$g]);
	//echo count($frsps);
	for ($p=0; $p<count($frsps); $p++) {
		if (!is_object($frsps[$p])) {
			continue;
		}
		//make package dirs
		$newdirlocation = $GLOBALS['sys_upload_dir'].'/'.$frsps[$p]->Group->getUnixName().'/'.$frsps[$p]->getFileName();
		$cmd="/bin/mkdir $newdirlocation";
		//echo "\n$cmd";
		if (!is_dir($newdirlocation)){
			exec($cmd,$out);
		}

		$frsrs =& $frsps[$p]->getReleases();

		for ($r=0; $r<count($frsrs); $r++) {
			if (!is_object($frsrs[$r])) {
				continue;
			}
			//make release dirs
			$newdirlocation = $GLOBALS['sys_upload_dir'].'/'.$frsrs[$r]->FRSPackage->Group->getUnixName().'/'.$frsrs[$r]->FRSPackage->getFileName().'/'.$frsrs[$r]->getFileName();
			$cmd="/bin/mkdir $newdirlocation";
			//echo "\n$cmd";
			if (!is_dir($newdirlocation)){
				exec($cmd,$out);
			}

			$frsfs =& $frsrs[$r]->getFiles();
			for ($f=0; $f<count($frsfs); $f++) {
				if (!is_object($frsfs[$f])) {
					continue;
				}
				$olddirlocation = $GLOBALS['sys_upload_dir'].'/'.$frsfs[$f]->FRSRelease->FRSPackage->Group->getUnixName().'/'.$frsfs[$f]->getName();
				$newdirlocation = $GLOBALS['sys_upload_dir'].'/'.$frsfs[$f]->FRSRelease->FRSPackage->Group->getUnixName().'/'.$frsfs[$f]->FRSRelease->FRSPackage->getFileName().'/'.$frsfs[$f]->FRSRelease->getFileName().'/';
				if (!is_file($newdirlocation.'/'.$frsfs[$f]->getName())) {
					$cmd="/bin/mv $olddirlocation $newdirlocation";
					//echo "\n$cmd";
					exec($cmd,$out);
				} else {
					//echo "Already Exists";
				}
			}
		}
	}
}

$cmd = '/bin/chown -R '.$GLOBALS["sys_apache_user"].':'.$GLOBALS["sys_apache_group"].' '.$GLOBALS['sys_upload_dir'];
exec($cmd,$out);

echo "SUCCESS";

?>
