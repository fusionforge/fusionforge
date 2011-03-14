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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

class SVNCommitEmailPlugin extends Plugin {

	function SVNCommitEmailPlugin () {
		$this->Plugin() ;
		$this->name = "svncommitemail" ;
		$this->text = "Source Code and Mailing List Integration" ;
		$this->hooks[] = "groupisactivecheckbox" ;
		$this->hooks[] = "groupisactivecheckboxpost" ;
	}

	function groupisactivecheckbox (&$params) {
		$group = group_get_object($params['group']);
		if ($group->usesPlugin('scmsvn') || $group->usesPlugin('websvn')) {
			parent::groupisactivecheckbox($params);
		} 
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
