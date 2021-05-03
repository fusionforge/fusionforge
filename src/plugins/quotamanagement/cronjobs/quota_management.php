<?php
/**
 * quota_management cronjob
 *
 * Copyright 2019, Franck Villaume - Capgemini
 * http://fusionforge.org
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

require_once dirname(__FILE__) . '/../../../www/env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/cron_utils.php';
require_once $gfcommon.'include/Group.class.php';

$quota_management = plugin_get_object('quota_management');

#compute the storage per project and per directory
$activegroups = group_get_active_projects();
if (forge_get_config('use_shell')) {
	foreach ($activegroups as $activegroup) {
		$ghome = forge_get_config('groupdir_prefix') . '/' . $activegroup->getUnixName();
		if (is_dir($ghome)) {
			#compute Group Home dir
			$dirsize = $quota_management->get_dir_size($ghome);
			$quota_management->setDirSize($activegroup->getID(), 'home', $dirsize);
		}
	}
}

if (forge_get_config('use_ftp')) {
	foreach ($activegroups as $activegroup) {
		$ftphome = forge_get_config('ftp_upload_dir') . '/' . $activegroup->getUnixName();
		if ($activegroup->usesFTP() && is_dir($ftphome)) {
			#compute FTP dir
			$dirsize = $quota_management->get_dir_size($ftphome);
			$quota_management->setDirSize($activegroup->getID(), 'ftp', $dirsize);
		}
	}
}

plugin_hook('quota_compute', array($activegroups));
