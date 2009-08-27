<?php
/** FusionForge CVS plugin
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

class CVSPlugin extends SCMPlugin {
	function CVSPlugin () {
		global $cvs_root;
		global $gfconfig;
		global $cvsdir_prefix ;
		$this->SCMPlugin () ;
		$this->name = 'scmcvs';
		$this->text = 'CVS';
		$this->hooks[] = 'scm_browser_page';
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_gather_stats' ;

		require_once $GLOBALS['gfconfig'].'plugins/scmcvs/config.php' ;

		$this->default_cvs_server = $default_cvs_server ;
		if ($cvs_root) {
			$this->cvs_root = $cvs_root;
		} elseif ($cvsdir_prefix) {
			$this->cvs_root = $cvsdir_prefix;
		} else {
			$this->cvs_root = "/cvsroot";
		} 
		$this->enabled_by_default = $enabled_by_default ;

		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_cvs_server;
	}

	function printShortStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin($this->name)) {
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
			echo ' (CVS: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}
	
	function getBlurb () {
		return _('<p>CVS documentation is available <a href="http://cvsbook.red-bean.com/">here</a>.</p>');
	}

	function getInstructionsForAnon ($project) {
		$cvsrootend = $project->getSCMBox().':'.$this->cvs_root.'/'.$project->getUnixName();
		$b = _('<p><b>Anonymous CVS Access</b></p><p>This project\'s CVS repository can be checked out through anonymous (pserver) CVS with the following instruction set. The module you wish to check out must be specified as the <i>modulename</i>. When prompted for a password for <i>anonymous</i>, simply press the Enter key.</p>');
		$b .= '<p>
		       <tt>cvs -d :pserver:anonymous@' . $cvsrootend.' login</tt><br/>
		       <tt>cvs -d :pserver:anonymous@' . $cvsrootend.' checkout <em>'._('modulename').'</em></tt>
		       </p>';

		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = _('<p><b>Developer CVS Access via SSH</b></p><p>Only project developers can access the CVS tree via this method. SSH must be installed on your client machine. Substitute <i>modulename</i> and <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
			$b .= '<p>
			       <tt>export CVS_RSH=ssh</tt><br/>
			       <tt>cvs -d :ext:<em>'._('developername').'</em>@'.$cvsrootend.' checkout <em>'._('modulename').'</em></tt>
			       </p>';

		return $b ;
	}

	function getSnapshotPara ($project) {
		global $sys_scm_snapshots_path ;
		$b = "" ;
		$filename = $project->getUnixName().'-scm-latest.tar.gz';
		if (file_exists($sys_scm_snapshots_path.'/'.$filename)) {
			$b .= '<p>[' ;
			$b .= util_make_link ("/snapshots.php?group_id=".$project->getID(),
					      _('Download the nightly snapshot')
				) ;
			$b .= ']</p>';
		}
		return $b ;
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('CVS Repository Browser'));
		$b .= _('<p>Browsing the CVS tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
				      _('Browse CVS Repository')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

	function getStatsBlock ($project) {
		global $HTML ;
		$b = '' ;

		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
					  array ($project->getID()));
		
		if (db_numrows($result) > 0) {
			$b .= $HTML->boxMiddle(_('Repository Statistics'));

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
				$b .= '<td width="50%">' ;
				$b .= util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
				$b .= '</td><td width="25%" align="right">'.$data['adds']. '</td>'.
					'<td width="25%" align="right">'.$data['commits'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['commits'] += $data['commits'];
				$i++;
			}
			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			$b .= '<td width="50%"><strong>'._('Total').':</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['adds']. '</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['commits'].'</strong></td>';
			$b .= '</tr>';
			$b .= $HTML->listTableBottom();
			$b .= '<hr size="1" />';
		}

		return $b ;
	}

	function printBrowserPage ($params) {
		global $HTML;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin ($this->name)) {
			if ($this->browserDisplayable ($project)) {
				print '<iframe src="'.util_make_url ("/scm/viewvc.php/?root=".$project->getUnixName()).'" frameborder="no" width=100% height=700></iframe>' ;
			}
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

		$repo = $this->cvs_root . '/' . $project->getUnixName() ;
		$locks_dir = $this->cvs_root . '/cvs-locks/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo) && is_dir ("$repo/CVSROOT")) {
			$repo_exists = true ;
		}
		
		if (!$repo_exists) {
			system ("cvs -d $repo init") ;
			system ("mkdir -p $locks_dir") ;
		}

		system ("chgrp -R $unix_group $repo $locks_dir") ;
		system ("chmod 3777 $locks_dir") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wXs,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wXs,o-rwx $repo") ;
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
			
			$repo = $this->cvs_root . '/' . $project->getUnixName() ;
			if (!is_dir ($repo) || !is_dir ("$repo/CVSROOT")) {
				echo "No repository\n" ;
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
				
			$hist_file =& fopen( $hist_file_path, 'r' );
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
				$u = &user_get_object_by_name ($last_user) ;
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
							     $usr_commit[$user] ? $usr_commit[$user] : 0,
							     $usr_add[$user] ? $usr_add[$user] : 0))) {
					echo "Error while inserting into stats_cvs_user\n" ;
					db_rollback () ;
					return false ;
				}
			}
		}
		db_commit();
	}

	function generateSnapshots ($params) {
		global $sys_scm_snapshots_path ;
		global $sys_scm_tarballs_path ;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$group_name = $project->getUnixName() ;

		$snapshot = $sys_scm_snapshots_path.'/'.$group_name.'-scm-latest.tar.gz';
		$tarball = $sys_scm_tarballs_path.'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)
		    || ! $project->enableAnonSCM()) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false;
		}

		$toprepo = $this->cvs_root ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo) && is_dir ("$repo/CVSROOT")) {
			$repo_exists = true ;
		}
		
		if (!$repo_exists) {
			unlink ($snapshot) ;
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
		system ("cd $tmp/$dir ; cvs -d $repo checkout . > /dev/null 2>&1") ;
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
