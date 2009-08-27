<?php
/** FusionForge Bazaar plugin
 *
 * Copyright 2009, Roland Mas
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

class BzrPlugin extends SCMPlugin {
	function BzrPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmbzr';
		$this->text = 'Bazaar';

		require_once $gfconfig.'plugins/scmbzr/config.php' ;
		
		$this->default_bzr_server = $default_bzr_server ;
		$this->enabled_by_default = $enabled_by_default ;
		$this->bzr_root = $bzr_root;

		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_bzr_server ;
	}

	function getBlurb () {
		return _('<p>Documentation for Bazaar (sometimes referred to as "bzr") is available <a href="http://bazaar-vcs.org/Documentation">here</a>.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Bazaar Access</b></p><p>This project\'s Bazaar repository can be checked out through anonymous access with the following command(s).</p>');
		$b .= '<p>' ;
		$b .= '<tt>bzr checkout http://'.$project->getSCMBox().$this->bzr_root.'/'.$project->getUnixName().'/'._('branchname').'</tt><br />';
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = '' ;
		$b .= _('<p><b>Developer Bazaar Access via SSH</b></p><p>Only project developers can access the Bazaar branches via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
		$b .= '<p><tt>bzr checkout bzr+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . $this->bzr_root .'/'. $project->getUnixName().'/'._('branchname').'</tt></p>' ;
		
		return $b ;
	}

	function getSnapshotPara ($project) {
		return ;
	}

	function getBrowserBlock ($project) {
		return ;
	}

	function getStatsBlock ($project) {
		return ;
	}

	function createOrUpdateRepo ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
				
		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$repo = $this->bzr_root . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo)) {
			$pipe = popen ("bzr info $repo 2>/dev/null", "r") ;
			$line = fgets ($pipe) ;
			fclose ($pipe) ;
		
			if (preg_match ("/^Shared repository/", $line) != 0) {
				$repo_exists = true ;
			}
		}
		
		if (!$repo_exists) {
			system ("bzr init-repo --no-trees $repo") ;
		}

		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wXs,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wXs,o-rwx $repo") ;
		}
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
