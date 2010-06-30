<?php
/** FusionForge Mercurial (Hg) plugin
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

class HgPlugin extends SCMPlugin {
	function HgPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmhg';
		$this->text = 'Mercurial';
		$this->hooks[] = 'scm_generate_snapshots' ;
		
		require $gfconfig.'plugins/scmhg/config.php' ;
		
		$this->default_hg_server = $default_hg_server ;
		if (isset ($hg_root)) {
			$this->hg_root = $hg_root;
		} else {
			$this->hg_root = $GLOBALS['sys_chroot'].'/scmrepos/hg' ;
		}
		
		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_hg_server ;
	}

	function getBlurb () {
		return _('<p>This Mercurial plugin is not completed yet.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Mercurial Access</b></p><p>This project\'s Mercurial repository can be checked out through anonymous access with the following command.</p>');
		$b .= '<p>' ;
		$b .= '<tt>hg clone '.util_make_url ('/anonscm/hg/'.$project->getUnixName().'/').'</tt><br />';
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		if (session_loggedin()) {
			$u =& user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b = _('<p><b>Developer Mercurial Access via SSH</b></p><p>Only project developers can access the Mercurial tree via this method. SSH must be installed on your client machine. Enter your site password when prompted.</p>');
			$b .= '<p><tt>hg clone ssh://'.$d.'@' . $project->getSCMBox() . $this->hg_root .'/'. $project->getUnixName().'/ .</tt></p>' ;
		} else {
			$d = '<i>developername</i>';
			$b = _('<p><b>Developer Mercurial Access via SSH</b></p><p>Only project developers can access the Mercurial tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper value. Enter your site password when prompted.</p>');
			$b .= '<p><tt>hg clone ssh://'.$d.'@' . $project->getSCMBox() . $this->hg_root .'/'. $project->getUnixName().'/ .</tt></p>' ;
		}
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

		$repo = $this->hg_root . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		system ("mkdir -p $repo") ;
		if (!is_dir ("$repo/.hg")) {
			system ("hg init $repo") ;
			system ("find $repo -type d | xargs chmod g+s") ;
		}

		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wX,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wX,o-rwx $repo") ;
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

		$toprepo = $this->hg_root ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo)) {
			unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
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
