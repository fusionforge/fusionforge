<?php
/**
 *
 * Copyright 2014,2015, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';

ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);

$globalStatus = 0;

if (class_exists('ZipArchive')) {
	#select project using FRS
	$packagesRes = db_query_params('select frs_package.package_id as pid, frs_package.name as pname, groups.group_id as gid, groups.unix_group_name as guxname
					from frs_package, groups where frs_package.group_id = groups.group_id and groups.status = $1 and groups.use_frs = $2',
					array('A', 1));

	while ($packageArr = db_fetch_array($packagesRes)) {
		$releaseRes = db_query_params('select release_id as rid, name as rname from frs_release'
					      . ' where release_id = (select max(release_id) from frs_release where package_id = $1)',
					      array($packageArr['pid']));
		$releaseArr = db_fetch_array($releaseRes);
		$filesRes = db_query_params('select filename from frs_file where release_id = $1', array($releaseArr['rid']));
		$packageArr['pname'] = util_secure_filename($packageArr['pname']);
		$releaseArr['rname'] = util_secure_filename($releaseArr['rname']);
		if (db_numrows($filesRes)) {
			$zip = new ZipArchive();
			$zipPath = forge_get_config('upload_dir').'/'.$packageArr['guxname'].'/'.$packageArr['pname'].'/'.$packageArr['pname'].'-latest.zip';
			if (is_dir(forge_get_config('upload_dir').'/'.$packageArr['guxname'].'/'.$packageArr['pname'])) {
				if (!is_file($zipPath)) {
					if ($zip->open($zipPath, ZIPARCHIVE::CREATE) !== true) {
						echo _('ERROR')._(': ')._('Cannot open the file archive')._(': ').$zipPath."\n";
						$globalStatus = 1;
					} else {
						$filesPath = forge_get_config('upload_dir').'/'.$packageArr['guxname'].'/'.$packageArr['pname'].'/'.$releaseArr['rname'];
						while ($fileArr = db_fetch_array($filesRes)) {
							$filePath = $filesPath.'/'.$fileArr['filename'];
							if (is_file($filePath)) {
								if ($zip->addFile($filePath, $fileArr['filename']) !== true) {
									echo _('ERROR')._(': ')._('Cannot add file to the file archive')._(': ').$filePath.' -> '.$zipPath."\n";
									$globalStatus = 1;
								}
							} else {
								echo _('WARNING')._(': ')._('File exists in database but folder does not')._(': ').$fileArr['filename']."\n";
								$globalStatus = 1;
							}
						}
						db_free_result($filesRes);
						if ($zip->close() !== true) {
							echo _('ERROR')._(': ')._('Cannot close the file archive')._(': ').$zipPath."\n";
							$globalStatus = 1;
						}
					}
					if (!is_file($zipPath)) {
						echo _('ERROR')._(': ')._('Something went wrong during zip creation, check permission?').' '.$zipPath."\n";
						$globalStatus = 1;
					} else {
						chown($zipPath, forge_get_config('apache_user'));
						chgrp($zipPath, forge_get_config('apache_group'));
					}
				}
			} else {
				echo _('WARNING')._(': ')._('Package exists in database but folder does not')._(': ').$packageArr['pname']."\n";
				$globalStatus = 1;
			}
		}
		db_free_result($releaseRes);
	}
	db_free_result($packagesRes);
}

if ($globalStatus) {
	echo "ERROR or WARNING during zip latest generation\n";
	exit(1);
}
echo "SUCCESS\n";
exit(0);
