<?php
/**
 * scmhook commitEmail Plugin Class
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
 * Copyright 2012, Benoit Debaenst - TrivialDev
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
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Commit Email";
		$this->description = _('Commit message log is pushed to commit mailing-list of the project');
		$this->classname = "commitEmail";
		$this->hooktype = "post-commit";
		$this->label = "scmsvn";
		$this->unixname = "commitemail";
		$this->needcopy = 0;
		$this->command = '/usr/bin/php -d include_path='.ini_get('include_path').' '.forge_get_config('plugins_path').'/scmhook/library/'
			.$this->label.'/hooks/'.$this->unixname.'/commit-email.php "$1" "$2" '.$this->group->getUnixName().'-commits@'.forge_get_config('lists_host');
	}

	function isAvailable() {
		global $gfcommon;
		require_once $gfcommon.'mail/MailingList.class.php';
		require_once $gfcommon.'mail/MailingListFactory.class.php';
		if ($this->group->usesMail()) {
			$mlFactory = new MailingListFactory($this->group);
			$mlArray = $mlFactory->getMailingLists();
			$mlCount = count($mlArray);
			for ($j = 0; $j < $mlCount; $j++) {
				$currentList =& $mlArray[$j];
				if ($currentList->getListEmail() == $this->group->getUnixName().'-commits@'.forge_get_config('lists_host'))
					return true;
			}
			$this->disabledMessage = _('Hook not available due to missing dependency: Project has no commit mailing-list: ').$this->group->getUnixName().'-commits';
		} else {
			$this->disabledMessage = _('Hook not available due to missing dependency: Project not using mailing-list.');
		}
		return false;
	}

	function getDisabledMessage() {
		return $this->disabledMessage;
	}
}
