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

forge_define_config_item ('default_server', 'scmhg', forge_get_config ('web_host')) ;
forge_define_config_item ('repos_path', 'scmhg', forge_get_config('chroot').'/scmrepos/hg') ;

class HgPlugin extends SCMPlugin {
	function HgPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmhg';
		$this->text = 'Mercurial';
		$this->hooks[] = 'scm_generate_snapshots' ;

		$this->register () ;
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmhg') ;
	}

	function getBlurb () {
		return '<p>' . _('This Mercurial plugin is not completed yet.') . '</p>';
	}

	function getInstructionsForAnon ($project) {
		$b = '<h2>' ;
		$b .=  _('Anonymous Mercurial Access');
		$b .= '</h2>' ;
		$b .= '<p>' ;
		$b .= 'This project\'s Mercurial repository can be checked out through anonymous access with the following command.';
		$b .= '</p>' ;
		$b .= '<p>' ;
		$b .= '<tt>hg clone '.util_make_url ('/anonscm/hg/'.$project->getUnixName().'/').'</tt><br />';
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		if (session_loggedin()) {
			$u =& user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b = '<h2>' ;
			$b .= _('Developer Mercurial Access via SSH');
			$b .= '</h2>' ;
			$b .= '<p>' ;
			$b .= _('Only project developers can access the Mercurial tree via this method. SSH must be installed on your client machine. Enter your site password when prompted.');
			$b .= '</p>' ;
			$b .= '<p><tt>hg clone ssh://'.$d.'@' . $project->getSCMBox() . forge_get_config('repos_path', 'scmhg') .'/'. $project->getUnixName().'/ .</tt></p>' ;
		} else {
			$d = '<i>developername</i>';
			$b = '</h2>' ;
			$b .= _('Developer Mercurial Access via SSH');
			$b .= '</h2>' ;
			$b .= '<p>' ;
			$b .= _('Only project developers can access the Mercurial tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper value. Enter your site password when prompted.');
			$b .= '</p>' ;
			$b .= '<p><tt>hg clone ssh://'.$d.'@' . $project->getSCMBox() . forge_get_config('repos_path', 'scmhg') .'/'. $project->getUnixName().'/ .</tt></p>' ;
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

		$repo = forge_get_config('repos_path', 'scmhg') . '/' . $project->getUnixName() ;
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

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		$group_name = $project->getUnixName() ;

		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar'.util_get_compressed_file_extension();

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($tarball) ;
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmhg') ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo)) {
			unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		system ("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
		chmod ("$tmp/tarball", 0644) ;
		copy ("$tmp/tarball", $tarball) ;
		unlink ("$tmp/tarball") ;
		system ("rm -rf $tmp") ;
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
