<?php
/** FusionForge Darcs plugin
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

class DarcsPlugin extends SCMPlugin {
	function DarcsPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmdarcs';
		$this->text = 'Darcs';
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_darcs_do_nothing' ;
		
		require_once $gfconfig.'plugins/scmdarcs/config.php' ;
		
		$this->default_darcs_server = $default_darcs_server ;
		$this->darcs_root = $darcs_root;
		
		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_darcs_server ;
	}

	function getBlurb () {
		return _('<p>This Darcs plugin is not fully implemented yet.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Darcs Access</b></p><p>This project\'s Darcs repository cannot be anonymously checked out yet.</p>');
		$b .= '<p>' ;
		$b .= '<tt>bzr checkout http://'.$project->getSCMBox().$this->bzr_root.'/'.$project->getUnixName().'/'._('branchname').'</tt><br />';
		$b .= '</p>';
		return $b ;
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = _('<p><b>Developer Darcs Access via SSH</b></p><p>Only project developers can access the Darcs tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
		$b .= '<p><tt>darcs get '.$project->getSCMBox() . ':'. $this->darcs_root .'/'. $project->getUnixName().'/ .</tt></p>' ;
		return $b ;
	}

	function getSnapshotPara ($project) {
		return ;
	}

	function getBrowserLinkBlock ($project) {
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

		$repo = $this->darcs_root . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		if (!is_dir ($repo."_darcs")) {
			system ("mkdir -p $repo") ;
			system ("cd $repo ; darcs init >/dev/null") ;
		}
		
		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wXs,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wXs,o-rwx $repo") ;
		}
	}

	function generateSnapshots ($params) {
		global $sys_scm_tarballs_path ;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$group_name = $project->getUnixName() ;

		$tarball = $sys_scm_tarballs_path.'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($tarball) ;
			return false;
		}

		$toprepo = $this->darcs_root ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo)) {
			unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		$today = date ('Y-m-d') ;
		$dir = $project->getUnixName ()."-$today" ;
		system ("mkdir -p $tmp/$dir") ;
		system ("cd $tmp ; darcs $repo $dir > /dev/null 2>&1") ;
		system ("tar czCf $tmp $tmp/snapshot.tar.gz $dir") ;
		chmod ("$tmp/snapshot.tar.gz", 0644) ;
		copy ("$tmp/snapshot.tar.gz", $snapshot) ;
		unlink ("$tmp/snapshot.tar.gz") ;
		system ("rm -rf $tmp/$dir") ;

		system ("tar czCf $toprepo $tmp/tarball.tar.gz " . $project->getUnixName()) ;
		chmod ("$tmp/tarball.tar.gz", 0644) ;
		copy ("$tmp/tarball.tar.gz", $tarball) ;
		unlink ("$tmp/tarball.tar.gz") ;
		system ("rm -rf $tmp") ;
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
