<?php
/**
 * scmhook postReceiveEmail Plugin Class
 * Copyright 2013, Benoit Debaenst - TrivialDev
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

class postReceiveEmail extends scmhook {
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Post Receive Email";
		$this->description = _('Commit message log is pushed to commit mailing-list of the project');
		$this->classname = "postReceiveEmail";
		$this->hooktype = "post-receive";
		$this->label = "scmgit";
		$this->unixname = "postreceiveemail";
		$this->needcopy = 0;
		$this->command = '/bin/sh '.forge_get_config('plugins_path').'/scmhook/library/'.$this->label.'/hooks/'.$this->unixname.'/postreceiveemail <<eoc '."\n".'$PARAMS'."\n".'eoc';
	}

	function isAvailable() {
		global $gfcommon;
		require_once $gfcommon.'mail/MailingList.class.php';
		require_once $gfcommon.'mail/MailingListFactory.class.php';

		if ($this->group->usesMail() && forge_get_config('use_ssh','scmgit')) {
			$mlFactory = new MailingListFactory($this->group);
			$mlArray = $mlFactory->getMailingLists();
			$mlCount = count($mlArray);
			for ($j = 0; $j < $mlCount; $j++) {
				$currentList =& $mlArray[$j];
				if ($currentList->getListEmail() == $this->group->getUnixName().'-commits@'.forge_get_config('lists_host'))
					return true;
			}
			$this->disabledMessage = _('Hook not available due to missing dependency: Project has no commit mailing-list: ').$this->group->getUnixName().'-commits';
		} elseif (!$this->group->usesMail()) {
			$this->disabledMessage = _('Hook not available due to missing dependency: Project not using mailing-list.');
		} elseif (!forge_get_config('use_ssh','scm_git')) {
			$this->disabledMessage = _('Hook not available due to missing dependency: Forge not using SSH for Git.');
		}
		return false;
	}

	function getDisabledMessage() {
		return $this->disabledMessage;
	}
}
