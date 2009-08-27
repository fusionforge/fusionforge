<?php
/**
 * FusionForge source control management
 *
 * Copyright 2004-2009, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/scm.php';

class SCMPlugin extends Plugin {
	/**
	 * SCMPlugin() - constructor
	 *
	 */
	function SCMPlugin () {
		$this->Plugin() ;
	}

	function register () {
		global $scm_list ;

		$scm_list[] = $this->name ;
	}

	function browserDisplayable ($project) {
		if ($project->usesPlugin($this->name)
		    && $project->enableAnonSCM()) {
			return true ;
		} else {
			return false ;
		}
	}

	function displayBrowser ($project) {
		if ($this->browserDisplayable ($project)) {
			// ...
		} else {
			return '' ;
		}
	}

	function createOrUpdateRepo ($params) {
		$group_id = $params['group_id'] ;

		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}
		
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		// ...
	}
		
	function gatherStats ($params) {
		$group_id = $params['group_id'] ;

		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}
		
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		// ...
	}
		
	function generateSnapshots ($params) {
		$group_id = $params['group_id'] ;

		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}

		$group_name = $project->getUnixName();

		$snapshot = $sys_scm_snapshots_path.'/'.$group_name.'-scm-latest.tar.gz';
		$tarball = $sys_scm_tarballs_path.'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)
		    || ! $project->enableAnonSCM()) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false;
		}

		// ...
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
