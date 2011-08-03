<?php
/** FusionForge CPOLD plugin
 *
 * Copyright 2009-2011, Roland Mas
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
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

forge_define_config_item ('default_server', 'scmcpold', forge_get_config ('web_host')) ;
forge_define_config_item ('repos_path', 'scmcpold', forge_get_config('chroot').'/scmrepos/cpold') ;

class CpoldPlugin extends SCMPlugin {
	function CpoldPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmcpold';
		$this->text = 'CPOLD';
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_cpold_do_nothing' ;

		$this->register () ;
	}

	function CallHook ($hookname, &$params) {
		global $HTML;

		switch ($hookname) {
		case 'scm_cpold_do_nothing':
			// Do nothing
			break;
		default:
			parent::CallHook ($hookname, $params) ;
		}
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmcpold');
	}

	function getBlurb () {
		return _('<p>This CPOLD plugin is only intended as a proof of concept.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  sprintf (_('<p><b>Anonymous CPOLD Access</b></p><p>This project\'s CPOLD repository can be accessep anonymously at %s.</p>'),
			       util_make_link ('/anonscm/cpold/'.$project->getUnixName().'/',
					       util_make_url ('/anonscm/cpold/'.$project->getUnixName().'/'))) ;
		return $b ;
	}

	function getInstructionsForRW ($project) {
		if (session_loggedin()) {
			$u =& user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b = _('<p><b>Developer CPOLD Access via SSH</b></p><p>Only project developers can access the CPOLD tree via this method. SSH must be installed on your client machine. Enter your site password when prompted.</p>');
			$b .= '<p><tt>scp -r '.$d.'@' . $project->getSCMBox() . ':'. forge_get_config('repos_path', 'scmcpold') .'/'. $project->getUnixName().'/ .</tt></p>' ;
		} else {
			$b = _('<p><b>Developer CPOLD Access via SSH</b></p><p>Only project developers can access the CPOLD tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper value. Enter your site password when prompted.</p>');
			$b .= '<p><tt>scp -r <i>'._('developername').'</i>@' . $project->getSCMBox() . ':'. forge_get_config('repos_path', 'scmcpold') .'/'. $project->getUnixName().'/ .</tt></p>' ;
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

		$repo = forge_get_config('repos_path', 'scmcpold') . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		system ("mkdir -p $repo") ;
		system ("chgrp -R $unix_group $repo") ;
		system ("find $repo -type d | xargs chmod g+s") ;

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

		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($tarball) ;
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmcpold') ;
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
