<?php
/**
 * scmhook commitEmail Plugin Class
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2012, Franck Villaume - TrivialDev
 * Copyright 2012, Benoit Debaenst - TrivialDev
 * Copyright 2014, Sylvain Beucler - Inria
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

class SvnCommitEmail extends scmhook {
	var $group;
	var $disabledMessage;

	function __construct() {
		$this->group = $GLOBALS['group'];
		$this->name = "Commit Email";
		$this->description = _('Commit message log is pushed to commit mailing-list of the project (which you need to create)');
		$this->classname = "commitEmail";
		$this->hooktype = "post-commit";
		$this->label = "scmsvn";
		$this->unixname = "commitemail";
		$this->needcopy = 0;
	}

	function getHookCmd() {
		$res = db_query_params('SELECT dest FROM plugin_scmhook_scmsvn_commitemail WHERE group_id=$1', array($this->group->getID()));
		if (db_numrows($res) > 0) {
			$dest = db_result($res, 0,0);
		} else {
			$params = $this->getParams();
			$dest = $params['dest']['default'];
		}
		return '/usr/bin/php -d include_path='.ini_get('include_path').' '.forge_get_config('plugins_path').'/scmhook/library/'
			.$this->label.'/hooks/'.$this->unixname.'/commit-email.php "$1" "$2" '.str_replace(',', ' ', $dest);
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
