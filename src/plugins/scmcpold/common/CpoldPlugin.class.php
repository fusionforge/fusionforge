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

forge_define_config_item ('default_server', 'scmcpold', forge_get_config ('scm_host')) ;
forge_define_config_item ('repos_path', 'scmcpold', forge_get_config('chroot').'/scmrepos/cpold') ;

class CpoldPlugin extends SCMPlugin {
	function __construct() {
		parent::__construct();
		$this->name = 'scmcpold';
		$this->text = 'CPOLD';
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_cpold_do_nothing' ;

		$this->register();
	}

	function CallHook($hookname, &$params) {
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
		return '<p>'._('This CPOLD plugin is only intended as a proof of concept.').'</p>';
	}

	function getInstructionsForAnon ($project) {
		$b = '<h2>';
		$b .=  _('Anonymous CPOLD Access');
		$b .= '</h2>';
		$b .= '<p>';
		$b .=  sprintf (_('This project\'s CPOLD repository can be accessed anonymously at %s.'),
			       util_make_link ('/anonscm/cpold/'.$project->getUnixName().'/',
				   util_make_url ('/anonscm/cpold/'.$project->getUnixName().'/'))) ;
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		if (session_loggedin()) {
			$u = user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b = '<h2>';
			$b .= sprintf(_('Developer %s Access via SSH'), 'CPOLD');
			$b .= '</h2>';
			$b .= '<p>';
			$b .= sprintf(_('Only project developers can access the %s tree via this method.'), 'CPOLD');
			$b .= ' ';
			$b .= _('SSH must be installed on your client machine.');
			$b .= ' ';
			$b .= _('Enter your site password when prompted.');
			$b .= '</p>';
			$b .= '<p><kbd>scp -r '.$d.'@' . $this->getBoxForProject($project) . ':'. forge_get_config('repos_path', 'scmcpold') .'/'. $project->getUnixName().'/ .</kbd></p>' ;
		} else {
			$b = '<h2>';
			$b .= sprintf(_('Developer %s Access via SSH'), 'CPOLD');
			$b .= '</h2>';
			$b .= '<p>';
			$b .= sprintf(_('Only project developers can access the %s tree via this method.'), 'CPOLD');
			$b .= ' ';
			$b .= _('SSH must be installed on your client machine.');
			$b .= ' ';
			$b .= _('Substitute <em>developername</em> with the proper value.');
			$b .= ' ';
			$b .= _('Enter your site password when prompted.');
			$b .= '</p>';
			$b .= '<p><kbd>scp -r <i>'._('developername').'</i>@' . $this->getBoxForProject($project) . ':'. forge_get_config('repos_path', 'scmcpold') .'/'. $project->getUnixName().'/ .</kbd></p>' ;
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

		if (! $project->enableAnonSCM()) {
			if (file_exists ($tarball)) {
				unlink ($tarball) ;
			}
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmcpold') ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo)) {
			if (file_exists ($tarball)) {
				unlink ($tarball) ;
			}
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

	function scm_admin_form(&$params) {
		global $HTML;
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}
		session_require_perm('project_admin', $params['group_id']);

		if (forge_get_config('allow_multiple_scm') && ($params['allow_multiple_scm'] > 1)) {
			echo html_ao('div', array('id' => 'tabber-'.$this->name, 'class' => 'tabbertab'));
		}
		if (forge_get_config('allow_multiple_scm') && ($params['allow_multiple_scm'] > 1)) {
			echo html_ac(html_ap() - 1);
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
