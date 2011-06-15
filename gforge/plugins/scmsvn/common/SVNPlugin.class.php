<?php
/** FusionForge Subversion plugin
 *
 * Copyright 2003-2009, Roland Mas
 * Copyright 2004, GForge, LLC
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

class SVNPlugin extends SCMPlugin {
	function SVNPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmsvn';
		$this->text = 'SVN';
		$this->hooks[] = 'scm_browser_page';
		$this->hooks[] = 'scm_update_repolist' ;
		$this->hooks[] = 'scm_generate_snapshots' ;
		$this->hooks[] = 'scm_gather_stats' ;

		require $gfconfig.'plugins/scmsvn/config.php' ;

		$this->default_svn_server = $default_svn_server ;
		$this->use_ssh = $use_ssh;
		$this->use_dav = $use_dav;
		$this->use_ssl = $use_ssl;
		if (isset ($svn_root)) {
			$this->svn_root = $svn_root;
		} else {
			$this->svn_root = $GLOBALS['sys_chroot'].'/scmrepos/svn' ;
		}

		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_svn_server ;
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
			echo ' (SVN: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}
	
	function getBlurb () {
		return _('<p>Documentation for Subversion (sometimes referred to as "SVN") is available <a href="http://svnbook.red-bean.com/">here</a>.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Subversion Access</b></p><p>This project\'s SVN repository can be checked out through anonymous access with the following command(s).</p>');
		$b .= '<p>' ;
		if ($this->use_ssh) {
			$b .= '<tt>svn checkout svn://'.$project->getSCMBox().$this->svn_root.'/'.$project->getUnixName().'/trunk</tt><br />';
		}
		if ($this->use_dav) {
			$url = 'http://' . $project->getSCMBox(). $this->svn_root .'/'. $project->getUnixName() .'/trunk';
			$b .= '<tt>svn checkout <a href="'.$url.'">'.$url.'</a> '.$project->getUnixName().'-trunk</tt>';
		}
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = '' ;
		if (session_loggedin()) {
			$u =& user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			if ($this->use_ssh) {
				$b .= _('<p><b>Developer Subversion Access via SSH</b></p><p>Only project developers can access the SVN tree via this method. SSH must be installed on your client machine. Enter your site password when prompted.</p>');
				$b .= '<p><tt>svn checkout svn+ssh://'.$d.'@' . $project->getSCMBox() . $this->svn_root .'/'. $project->getUnixName().'/trunk</tt></p>' ;
			}
			if ($this->use_dav) {
				$b .= _('<p><b>Developer Subversion Access via DAV</b></p><p>Only project developers can access the SVN tree via this method. Enter your site password when prompted.</p>');
				$url = 'http'.(($this->use_ssl) ? 's' : '').'://'. $project->getSCMBox() . $this->svn_root .'/'.$project->getUnixName().'/trunk' ;
				$b .= '<p><tt>svn checkout --username '.$d.' <a href="'.$url.'">'.$url.'</a> '.$project->getUnixName().'-trunk</tt></p>' ;
			}
		} else {
			if ($this->use_ssh) {
				$b .= _('<p><b>Developer Subversion Access via SSH</b></p><p>Only project developers can access the SVN tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
				$b .= '<p><tt>svn checkout svn+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . $this->svn_root .'/'. $project->getUnixName().'/trunk</tt></p>' ;
			}
			if ($this->use_dav) {
				$b .= _('<p><b>Developer Subversion Access via DAV</b></p><p>Only project developers can access the SVN tree via this method. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
				$url = 'http'.(($this->use_ssl) ? 's' : '').'://'. $project->getSCMBox() . $this->svn_root .'/'.$project->getUnixName().'/trunk' ;
				$b .= '<p><tt>svn checkout --username <i>'._('developername').'</i> <a href="'.$url.'">'.$url.'</a> '.$project->getUnixName().'-trunk</tt></p>' ;
			}
		}
		return $b ;
	}

	function getSnapshotPara ($project) {
		return ;
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Subversion Repository Browser'));
		$b .= _('<p>Browsing the Subversion tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
				      _('Browse Subversion Repository')
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

		$repo = $this->svn_root . '/' . $project->getUnixName() ;

		if (!is_dir ($repo) || !is_file ("$repo/format")) {
			system ("svnadmin create $repo") ;
			system ("svn mkdir -m'Init' file:///$repo/trunk file:///$repo/tags file:///$repo/branches") ;
		}

		if ($this->use_ssh) {
			$unix_group = 'scm_' . $project->getUnixName() ;
			system ("find $repo -type d | xargs chmod g+s") ;
			system ("chgrp -R $unix_group $repo") ;
			if ($project->enableAnonSCM()) {
				system ("chmod -R g+wX,o+rX-w $repo") ;
			} else {
				system ("chmod -R g+wX,o-rwx $repo") ;
			}
		} else {
			$unix_user = $GLOBALS['sys_apache_user'];
			$unix_group = $GLOBALS['sys_apache_group'];
			system ("chown -R $unix_user:$unix_group $repo") ;
			system ("chmod -R g-rwx,o-rwx $repo") ;
		}
	}

	function updateRepositoryList ($params) {
		global $sys_var_path ;
		$groups = $this->getGroups () ;

		// Update WebDAV stuff
		if (!$this->use_dav) {
			return true ;
		}

		$access_data = '' ;
		$password_data = '' ;

		$svnusers = array () ;
		foreach ($groups as $project) {
			if ( !$project->isActive()) {
				continue;
			}
			if ( !$project->usesSCM()) {
				continue;
			}
			$access_data .= '[' . $project->getUnixName () . ":/]\n" ;
			$users = $project->getMembers () ;
			foreach ($users as $user) {
				$perm_data = $project->getPermission ($user)->getPermData() ;
				$role = new Role($project,$perm_data['role_id']);
				$svnlevel = $role->getVal('scm',0);
				if ($svnlevel >= 0) {
					$svnusers[$user->getID()] = $user ;
					if ($svnlevel == 0) {
						$access_data .= $user->getUnixName() . "= r\n" ;
					} else {
						$access_data .= $user->getUnixName() . "= rw\n" ;
					}
				}
			}
			if ( $project->enableAnonSCM() ) {
				$access_data .= "anonsvn= r\n" ;
				$access_data .= "* = r\n" ;
				
			}
			$access_data .= "\n" ;
		}

		foreach ($svnusers as $user_id => $user) {
			$password_data .= $user->getUnixName().':'.$user->getUnixPasswd()."\n" ;
		}
		$password_data .= 'anonsvn:$apr1$Kfr69/..$J08mbyNpD81y42x7xlFDm.'."\n";

		$fname = $sys_var_path.'/svnroot-authfile' ;
		$f = fopen ($fname.'.new', 'w') ;
		fwrite ($f, $password_data) ;
		fclose ($f) ;
		chmod ($fname.'.new', 0644) ;
		rename ($fname.'.new', $fname) ;

		$fname = $sys_var_path.'/svnroot-access' ;
		$f = fopen ($fname.'.new', 'w') ;
		fwrite ($f, $access_data) ;
		fclose ($f) ;
		chmod ($fname.'.new', 0644) ;
		rename ($fname.'.new', $fname) ;
	}

	function gatherStats ($params) {
		global $last_user, $last_time, $last_tag, $time_ok, $start_time, $end_time,
			$adds, $deletes, $updates, $commits, $date_key,
			$usr_adds, $usr_deletes, $usr_updates;

		$time_ok = true ;
		
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
			$start_time = gmmktime( 0, 0, 0, $month, $day, $year);
			$end_time = $start_time + 86400;

			$updates = 0 ;
			$adds = 0 ;
			$usr_adds = array () ;
			$usr_updates = array () ;

			$repo = $this->svn_root . '/' . $project->getUnixName() ;
			if (!is_dir ($repo) || !is_file ("$repo/format")) {
				echo "No repository\n" ;
				db_rollback () ;
				return false ;
			}
	
                        $d1 = date ('Y-m-d', $start_time - 150000) ;
                        $d2 = date ('Y-m-d', $end_time + 150000) ;

			$pipe = popen ("svn log file://$repo --xml -v -q -r '".'{'.$d2.'}:{'.$d1.'}'."' 2> /dev/null", 'r' ) ;

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
	
			$xml_parser = xml_parser_create();
			xml_set_element_handler($xml_parser, "SVNPluginStartElement", "SVNPluginEndElement");
			xml_set_character_data_handler($xml_parser, "SVNPluginCharData");

			// Analyzing history stream
			while (!feof($pipe) &&
			       $data = fgets ($pipe, 4096)) {
				
				if (!xml_parse ($xml_parser, $data, feof ($pipe))) {
					debug("Unable to parse XML with error " .
					      xml_error_string(xml_get_error_code($xml_parser)) .
					      " on line " .
					      xml_get_current_line_number($xml_parser));
					db_rollback () ;
					return false ;
					break;
				}
			}
			
			xml_parser_free ($xml_parser);

			// inserting group results in stats_cvs_groups
			if ($updates > 0 || $adds > 0) {
				if (!db_query_params ('INSERT INTO stats_cvs_group (month,day,group_id,checkouts,commits,adds) VALUES ($1,$2,$3,$4,$5,$6)',
						      array ($month_string,
							     $day,
							     $project->getID(),
							     0,
							     $updates,
							     $adds))) {
					echo "Error while inserting into stats_cvs_group\n" ;
					db_rollback () ;
					return false ;
				}
			}
				
			// building the user list
			$user_list = array_unique( array_merge( array_keys( $usr_adds ), array_keys( $usr_updates ) ) );

			foreach ( $user_list as $user ) {
				// trying to get user id from user name
				$u = &user_get_object_by_name ($user) ;
				if ($u) {
					$user_id = $u->getID();
				} else {
					continue;
				}
		
				$uu = array_key_exists($user,$usr_updates) ? $usr_updates[$user] : 0 ;
				$ua = array_key_exists($user,$usr_adds) ? $usr_adds[$user] : 0 ;
				if ($uu > 0 || $ua > 0) {
					if (!db_query_params ('INSERT INTO stats_cvs_user (month,day,group_id,user_id,commits,adds) VALUES ($1,$2,$3,$4,$5,$6)',
							      array ($month_string,
								     $day,
								     $project->getID(),
								     $user_id,
								     $uu,
								     $ua))) {
						echo "Error while inserting into stats_cvs_user\n" ;
						db_rollback () ;
						return false ;
					}
				}
			}
			db_commit();
		}
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

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			if (is_file($snapshot)) {
				unlink ($snapshot) ;
			}
			if (is_file($tarball)) {
				unlink ($tarball) ;
			}
			return false;
		}

		$toprepo = $this->svn_root ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo) || !is_file ("$repo/format")) {
			if (is_file($snapshot)) {
				unlink ($snapshot) ;
			}
			if (is_file($tarball)) {
				unlink ($tarball) ;
			}
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		$today = date ('Y-m-d') ;
		$dir = $project->getUnixName ()."-$today" ;
		system ("mkdir -p $tmp") ;
		$code = 0 ;
		system ("svn ls file://$repo/trunk > /dev/null", $code) ;
		if ($code == 0) {
			system ("cd $tmp ; svn checkout file://$repo/trunk $dir > /dev/null 2>&1") ;
			system ("tar czCf $tmp $tmp/snapshot.tar.gz $dir") ;
			chmod ("$tmp/snapshot.tar.gz", 0644) ;
			copy ("$tmp/snapshot.tar.gz", $snapshot) ;
			unlink ("$tmp/snapshot.tar.gz") ;
			system ("rm -rf $tmp/$dir") ;
		} else {
			if (is_file($snapshot)) {
				unlink ($snapshot) ;
			}
		}

		system ("tar czCf $toprepo $tmp/tarball.tar.gz " . $project->getUnixName()) ;
		chmod ("$tmp/tarball.tar.gz", 0644) ;
		copy ("$tmp/tarball.tar.gz", $tarball) ;
		unlink ("$tmp/tarball.tar.gz") ;
		system ("rm -rf $tmp") ;
	}
  }

// End of class, helper functions now

function SVNPluginCharData ($parser, $chars) {
	global $last_tag, $last_user, $last_time, $start_time, $end_time,
		$time_ok, $user_list;
	switch ($last_tag) {
	case "AUTHOR":
		$last_user = ereg_replace ('[^a-z0-9_-]', '', 
					   strtolower (trim ($chars))) ;
		break;
	case "DATE":
		$chars = preg_replace('/T(\d\d:\d\d:\d\d)\.\d+Z?$/', ' ${1}', $chars);
		$last_time = strtotime($chars);
		if ($start_time <= $last_time && $last_time < $end_time) {
			$time_ok = true;
		} else {
			$time_ok = false;
		}
		break;
	}
}

function SVNPluginStartElement($parser, $name, $attrs) {
	global $last_user, $last_time, $last_tag, $time_ok,
		$adds, $updates, $usr_adds, $usr_updates;
	$last_tag = $name;
	switch($name) {
	case "LOGENTRY":
		// Make sure we clean up before doing a new log entry
		$last_user = "";
		$last_time = "";
		break;
	case "PATH":
		if ($time_ok) {
			if ($attrs['ACTION'] == "M") {
				$updates++;
				if ($last_user) {
					$usr_updates[$last_user]++;
				}
			} elseif ($attrs['ACTION'] == "A") {
				$adds++;
				if ($last_user) {
					$usr_adds[$last_user]++;
				}
			}
		}
		break;
	}
}

function SVNPluginEndElement ($parser, $name) {
	global $time_ok, $last_tag, $commits;
	if ($name == "LOGENTRY" && $time_ok) {
		$commits++;
	}
	$last_tag = "";
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
