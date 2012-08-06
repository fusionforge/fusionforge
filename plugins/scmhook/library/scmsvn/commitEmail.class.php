<?php
/**
 * scmhook commitEmail Plugin Class
 * Copyright 2011, Franck Villaume - Capgemini
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

global $gfplugins;
require_once $gfplugins.'scmhook/common/scmhook.class.php';

class commitEmail extends scmhook {
	function commitEmail() {
		$this->name = "Commit Email";
		$this->description = _('Commit is pushed to commit mailing-list of the project');
		$this->classname = "commitEmail";
		$this->hooktype = "post-commit";
		$this->label = "scmsvn";
		$this->unixname = "commitemail";
		$this->needcopy = 0;
		$this->command = '/usr/bin/php -d include_path='.ini_get('include_path').' '.forge_get_config('plugins_path').'/scmhook/library/'.
				$this->label.'/hooks/'.$this->unixname.'/commit-email.php "$1" "$2" '.$GLOBALS['group']->getUnixName().'-commits@'.forge_get_config('lists_host');
	}
}
?>
