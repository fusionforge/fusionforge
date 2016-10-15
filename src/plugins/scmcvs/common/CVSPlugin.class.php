<?php
/**
 * FusionForge CVS plugin
 *
 * Copyright 2004-2009, Roland Mas
 * Copyright 2013,2016, Franck Villaume - TrivialDev
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

forge_define_config_item ('default_server', 'scmcvs', forge_get_config ('scm_host')) ;
forge_define_config_item ('repos_path', 'scmcvs', forge_get_config('chroot').'/scmrepos/cvs') ;

class CVSPlugin extends SCMPlugin {
	function __construct () {
		global $cvs_root;
		global $gfconfig;
		global $cvsdir_prefix;
		parent::__construct();
		$this->name = 'scmcvs';
		$this->text = _('CVS');
		$this->pkg_desc =
_("This plugin contains the CVS subsystem of FusionForge. It allows each
FusionForge project to have its own CVS repository, and gives some control
over it to the project's administrator.");
		$this->hooks[] = 'scm_browser_page';
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_gather_stats' ;

		$this->provides['cvs'] = true;

		$this->register () ;
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmcvs');
	}

	function printShortStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name) && forge_check_perm('scm', $project->getID(), 'read')) {
			$result = db_query_params('SELECT sum(commits) AS commits, sum(adds) AS adds FROM stats_cvs_group WHERE group_id=$1',
						  array ($project->getID())) ;
			$commit_num = db_result($result,0,'commits');
			$add_num    = db_result($result,0,'adds');
			if (!$commit_num) {
				$commit_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			$params['result'] .= ' (CVS: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}

	function getBlurb () {
		return '<p>'
				. sprintf(_('Documentation for %1$s is available at <a href="%2$s">%2$s</a>.'),
							'CVS',
							'http://cvsbook.red-bean.com/')
				. '</p>';
	}

	function getInstructionsForAnon ($project) {
		$cvsrootend = $this->getBoxForProject($project).':'.forge_get_config('repos_path', 'scmcvs').'/'.$project->getUnixName();
		$b = '<h2>' . _('Anonymous CVS Access') . '</h2>';
		$b .= '<p>';
		$b .= _('This project\'s CVS repository can be checked out through anonymous (pserver) CVS with the following instruction set. The module you wish to check out must be specified as the <em>modulename</em>. When prompted for a password for <em>anonymous</em>, simply press the Enter key.');
		$b .= '</p>';
		$b .= '<p>
			<tt>cvs -d :pserver:anonymous@' . $cvsrootend.' login</tt><br/>
			<tt>cvs -d :pserver:anonymous@' . $cvsrootend.' checkout <em>'._('modulename').'</em></tt>
			</p>';

		return $b ;
	}

	function getInstructionsForRW ($project) {
		$cvsrootend = $this->getBoxForProject($project).':'.forge_get_config('repos_path', 'scmcvs').'/'.$project->getUnixName();
		if (session_loggedin()) {
			$u = user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b = '<h2>';
			$b .= sprintf(_('Developer %s Access via SSH'), 'CVS');
			$b = '</h2>';
			$b .= '<p>';
			$b .= sprintf(_('Only project developers can access the %s tree via this method.'), 'CVS');
			$b .= ' ';
			$b .= _('SSH must be installed on your client machine.');
			$b .= ' ';
			$b .= _('Substitute <em>developername</em> with the proper value.');
			$b .= ' ';
			$b .= _('Enter your site password when prompted.');
			$b .= '</p>';
			$b .= '<p>
				<tt>export CVS_RSH=ssh</tt><br/>
				<tt>cvs -d :ext:'.$d.'@'.$cvsrootend.' checkout <em>'._('modulename').'</em></tt>
				</p>';
		} else {
			$b = '<h2>';
			$b .= sprintf(_('Developer %s Access via SSH'), 'CVS');
			$b = '</h2>';
			$b .= '<p>';
			$b .= sprintf(_('Only project developers can access the %s tree via this method.'), 'CVS');
			$b .= ' ';
			$b .= _('SSH must be installed on your client machine.');
			$b .= ' ';
			$b .= _('Substitute <em>developername</em> with the proper value.');
			$b .= ' ';
			$b .= _('Enter your site password when prompted.');
			$b .= '</p>';
			$b .= '<p>
				<tt>export CVS_RSH=ssh</tt><br/>
				<tt>cvs -d :ext:<em>'._('developername').'</em>@'.$cvsrootend.' checkout <em>'._('modulename').'</em></tt>
				</p>';
		}
		return $b ;
	}

	function getSnapshotPara ($project) {

		$b = "" ;
		$filename = $project->getUnixName().'-scm-latest.tar'.util_get_compressed_file_extension();
		if (file_exists(forge_get_config('scm_snapshots_path').'/'.$filename)) {
			$b .= '<p>[' ;
			$b .= util_make_link ("/snapshots.php?group_id=".$project->getID(),
					      _('Download the nightly snapshot')
				) ;
			$b .= ']</p>';
		}
		return $b ;
	}

	function getBrowserLinkBlock($project) {
		$b = html_e('h2', array(), _('CVS Repository Browser'));
		$b .= '<p>';
		$b .= sprintf(_("Browsing the %s tree gives you a view into the current status of this project's code."), 'CVS');
		$b .= ' ';
		$b .= _('You may also view the complete histories of any file in the repository.');
		$b .= '</p>';
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
								sprintf(_('Browse %s Repository'), 'CVS')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

	function getStatsBlock($project) {
		global $HTML;
		$b = '';

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
					  array ($project->getID()));

		if (db_numrows($result) > 0) {
			$tableHeaders = array(
				_('Name'),
				_('Adds'),
				_('Commits')
				);
			$b .= $HTML->listTableTop($tableHeaders);

			$i = 0;
			$total = array('adds' => 0, 'commits' => 0);

			while($data = db_fetch_array($result)) {
				$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				$b .= '<td class="halfwidth">' ;
				$b .= util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
				$b .= '</td><td class="onequarterwidth align-right">'.$data['adds']. '</td>'.
					'<td class="onequarterwidth align-right">'.$data['commits'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['commits'] += $data['commits'];
				$i++;
			}
			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			$b .= '<td class="halfwidth"><strong>'._('Total').':</strong></td>'.
				'<td class="onequarterwidth align-right"><strong>'.$total['adds']. '</strong></td>'.
				'<td class="onequarterwidth align-right"><strong>'.$total['commits'].'</strong></td>';
			$b .= '</tr>';
			$b .= $HTML->listTableBottom();
		} else {
			$b .= $HTML->information(_('No history yet'));
		}

		return $b ;
	}

	function printBrowserPage ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name)) {
			$iframe_src = '/scm/viewvc.php?root='.$project->getUnixName();
			htmlIframe($iframe_src, array('id'=>'scmcvs_iframe'));
		}
	}

	function createOrUpdateRepo ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		$repo_prefix = forge_get_config('repos_path', 'scmcvs');
		if (!is_dir($repo_prefix) && !mkdir($repo_prefix, 0755, true)) {
			return false;
		}

		$repo = $repo_prefix . '/' . $project->getUnixName() ;
		$locks_dir = $repo_prefix . '/cvs-locks/' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo) && is_dir ("$repo/CVSROOT")) {
			$repo_exists = true ;
		}

		$rw_unix_group = $project->getUnixName() . '_scmrw';
		$ro_unix_group = $project->getUnixName() . '_scmro';
		$apache_user = forge_get_config('apache_user');

		if (!$repo_exists) {
			if (!@mkdir($repo, 0700)) {
				return false;
			}
			$ret = 0;
			system ("cvs -d $repo init", $ret) ;
			if ($ret != 0) {
				return false;
			}
			system ("mkdir -p $locks_dir") ;
			system ("chgrp $unix_group $locks_dir") ;
			system ("chmod 3777 $locks_dir") ;

			$cvs_binary_version = get_cvs_binary_version();
			if ($cvs_binary_version == '1.12') {
				util_create_file_with_contents("$repo/CVSROOT/config", "SystemAuth=no\nLockDir=$locks_dir\nUseNewInfoFmtStrings=yes\n");
			}
			if ($cvs_binary_version == '1.11') {
				util_create_file_with_contents("$repo/CVSROOT/config", "SystemAuth=no\nLockDir=$locks_dir\n");
			}
			system ("chmod -R g+rwXs,o+rX-w $repo") ;
			if ($project->enableAnonSCM()) {
				util_create_file_with_contents ("$repo/CVSROOT/readers", "anonymous\n");
				util_create_file_with_contents ("$repo/CVSROOT/passwd", "anonymous::$apache_user\n");
			} else {
				util_create_file_with_contents ("$repo/CVSROOT/readers", "\n");
				util_create_file_with_contents ("$repo/CVSROOT/passwd", "\n");
			}
			system ("chgrp -R $rw_unix_group $repo") ;
			system ("chgrp $ro_unix_group $repo");
		}

		if ($project->enableAnonSCM()) {
			util_create_file_with_contents ("$repo/CVSROOT/readers", "anonymous\n");
			util_create_file_with_contents ("$repo/CVSROOT/passwd", "anonymous::$apache_user\n");
			system ("chmod g+rwXs,o+rX-w $repo") ;
		} else {
			util_create_file_with_contents ("$repo/CVSROOT/readers", "\n");
			util_create_file_with_contents ("$repo/CVSROOT/passwd", "\n");
			system ("chmod g+rwXs,o-rwx $repo") ;
		}
	}

	function gatherStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if ($params['mode'] == 'day') {
			db_begin();

			$year = $params ['year'] ;
			$month = $params ['month'] ;
			$day = $params ['day'] ;
			$month_string = sprintf( "%04d%02d", $year, $month );
			$day_begin = gmmktime( 0, 0, 0, $month, $day, $year);
			$day_end = $day_begin + 86400;

			$repo = forge_get_config('repos_path', 'scmcvs') . '/' . $project->getUnixName() ;
			if (!is_dir ($repo) || !is_dir ("$repo/CVSROOT")) {
				echo "No repository $repo\n" ;
				db_rollback () ;
				return false ;
			}

			$cvs_co	= 0;
			$cvs_commit = 0;
			$cvs_add = 0;
			$usr_commit = array();
			$usr_add = array();

			$hist_file_path = $repo.'/CVSROOT/history';
			if (!file_exists($hist_file_path)
			    || !is_readable($hist_file_path)
			    || filesize($hist_file_path) == 0) {
				// echo "No history file\n" ;
				db_rollback () ;
				return false ;
			}

			$hist_file = fopen( $hist_file_path, 'r' );
			if ( ! $hist_file ) {
				echo "Unreadable history\n" ;
				db_rollback () ;
				return false ;
			}

			// cleaning stats_cvs_* table for the current day
			$res = db_query_params ('DELETE FROM stats_cvs_group WHERE month=$1 AND day=$2 AND group_id=$3',
						array ($month_string,
						       $day,
						       $project->getID())) ;
			if(!$res) {
				echo "Error while cleaning stats_cvs_group\n" ;
				db_rollback () ;
				return false ;
			}

			$res = db_query_params ('DELETE FROM stats_cvs_user WHERE month=$1 AND day=$2 AND group_id=$3',
						array ($month_string,
						       $day,
						       $project->getID())) ;
			if(!$res) {
				echo "Error while cleaning stats_cvs_user\n" ;
				db_rollback () ;
				return false ;
			}

			// analyzing history file
			while (!feof($hist_file)) {
				$hist_line = fgets($hist_file, 1024);
				if ( preg_match( '/^\s*$/', $hist_line ) ) {
					continue;
				}
				list( $cvstime,$user,$curdir,$module,$rev,$file ) = explode( '|', $hist_line );

				$type = substr($cvstime, 0, 1);
				$time_parsed = hexdec( substr($cvstime, 1, 8) );

				if ( ($time_parsed > $day_begin) && ($time_parsed < $day_end) ) {
					if ( $type == 'M' ) {
						$cvs_commit++;
						if(!isset($usr_commit[$user])) $usr_commit[$user] = 0;
						$usr_commit[$user]++;
					} elseif ( $type == 'A' ) {
						$cvs_add++;
						if(!isset($usr_add[$user])) $usr_add[$user] = 0;
						$usr_add[$user]++;
					} elseif ( $type == 'O' || $type == 'E' ) {
						$cvs_co++;
						// ignoring checkouts on a per-user
					}
				} elseif ( $time_parsed > $day_end ) {
					break;
				}
			}
			fclose( $hist_file );

			// inserting group results in stats_cvs_groups
			if (!db_query_params ('INSERT INTO stats_cvs_group (month,day,group_id,checkouts,commits,adds) VALUES ($1,$2,$3,$4,$5,$6)',
					      array ($month_string,
						     $day,
						     $project->getID(),
						     $cvs_co,
						     $cvs_commit,
						     $cvs_add))) {
				echo "Error while inserting into stats_cvs_group\n" ;
				db_rollback () ;
				return false ;
			}

			// building the user list
			$user_list = array_unique( array_merge( array_keys( $usr_add ), array_keys( $usr_commit ) ) );

			foreach ( $user_list as $user ) {
				// trying to get user id from user name
				$u = user_get_object_by_name ($user) ;
				if ($u) {
					$user_id = $u->getID();
				} else {
					continue;
				}

				if (!db_query_params ('INSERT INTO stats_cvs_user (month,day,group_id,user_id,commits,adds) VALUES ($1,$2,$3,$4,$5,$6)',
						      array ($month_string,
							     $day,
							     $project->getID(),
							     $user_id,
							     isset ($usr_commit[$user]) ? $usr_commit[$user] : 0,
							     isset ($usr_add[$user]) ? $usr_add[$user] : 0))) {
					echo "Error while inserting into stats_cvs_user\n" ;
					db_rollback () ;
					return false ;
				}
			}
		}
		db_commit();
	}

	function generateSnapshots ($params) {
		$us = forge_get_config('use_scm_snapshots') ;
		$ut = forge_get_config('use_scm_tarballs') ;
		if (!$us && !$ut) {
			return false ;
		}

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		$group_name = $project->getUnixName() ;

		$snapshot = forge_get_config('scm_snapshots_path').'/'.$group_name.'-scm-latest.tar'.util_get_compressed_file_extension();
		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar'.util_get_compressed_file_extension();

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			if (file_exists($snapshot)) unlink ($snapshot) ;
			if (file_exists($tarball)) unlink ($tarball) ;
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmcvs') ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo) && is_dir ("$repo/CVSROOT")) {
			$repo_exists = true ;
		}

		if (!$repo_exists) {
			if (file_exists($snapshot)) unlink ($snapshot) ;
			if (file_exists($tarball)) unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		if ($us) {
			$today = date ('Y-m-d') ;
			$dir = $project->getUnixName ()."-$today" ;
			system ("mkdir -p $tmp/$dir") ;
			system ("cd $tmp/$dir ; cvs -d $repo export -D now . > /dev/null 2>&1") ;
			system ("tar cCf $tmp - $dir |".forge_get_config('compression_method')."> $tmp/snapshot") ;
			chmod ("$tmp/snapshot", 0644) ;
			copy ("$tmp/snapshot", $snapshot) ;
			unlink ("$tmp/snapshot") ;
			system ("rm -rf $tmp/$dir") ;
		}

		if ($ut) {
			system ("tar cCf $toprepo - ".$project->getUnixName() ."|".forge_get_config('compression_method')."> $tmp/tarball") ;
			chmod ("$tmp/tarball", 0644) ;
			copy ("$tmp/tarball", $tarball) ;
			unlink ("$tmp/tarball") ;
			system ("rm -rf $tmp") ;
		}
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
