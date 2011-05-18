<?php

/**
 * SVNCommitEmailPlugin Class
 *
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

class SVNCommitEmailPlugin extends Plugin {

	function SVNCommitEmailPlugin () {
		$this->Plugin() ;
		$this->name = "svncommitemail" ;
		$this->text = "Source Code and Mailing List Integration" ;
		$this->hooks[] = "groupisactivecheckbox" ;
		$this->hooks[] = "groupisactivecheckboxpost" ;
		$this->hooks[] = "cmd_for_post_commit_hook";
	}

	function groupisactivecheckbox (&$params) {
		$group = group_get_object($params['group']);
		if ($group->usesPlugin('scmsvn') || $group->usesPlugin('websvn')) {
			parent::groupisactivecheckbox($params);
		} 
	}

	function cmd_for_post_commit_hook (&$params) {
		$params['hooks'][$this->name] =  '/usr/bin/php -d include_path='.ini_get('include_path').
			' '.forge_get_config('plugins_path').'/svncommitemail/bin/commit-email.php '.$params['repos'].' "$2" '.
			$params['unix_group_name'].'-commits@'.forge_get_config('lists_host');
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
