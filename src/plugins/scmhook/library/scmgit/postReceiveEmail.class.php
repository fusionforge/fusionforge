<?php
/**
 * scmhook GitPostReceiveEmail Plugin Class
 * Copyright 2013, Benoit Debaenst - TrivialDev
 * Copyright 2016,2018, Franck Villaume - TrivialDev
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

class GitPostReceiveEmail extends scmhook {
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Post Receive Email";
		$this->description = _('Commit message log is pushed to commit mailing-list of the project (which you need to create)');
		$this->classname = "postReceiveEmail";
		$this->hooktype = "post-receive";
		$this->label = "scmgit";
		$this->unixname = "postreceiveemail";
		$this->needcopy = 0;
		$this->command = forge_get_config('plugins_path').'/scmhook/library/'.$this->label.'/hooks/'.$this->unixname.'/post-receive-email <<eoc '."\n".'$PARAMS'."\n".'eoc';
	}

	function getDisabledMessage() {
		return $this->disabledMessage;
	}

	function getParams() {
		return array(
			'dest' => array(
				'description' => _('Send commit e-mail notification to'),
				'type'        => 'emails',
				'default'     => $this->group->getUnixName().'-commits@'.forge_get_config('lists_host'),
			)
		);
	}
}
