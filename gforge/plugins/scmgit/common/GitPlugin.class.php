<?php
/** FusionForge Git plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2009, Mehdi Dogguy <mehdi@debian.org>
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

class GitPlugin extends SCMPlugin {
	function GitPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmgit';
		$this->text = 'Git';
		$this->hooks[] = 'scm_update_repolist' ;
		$this->hooks[] = 'scm_browser_page' ;
		$this->hooks[] = 'scm_gather_stats' ;
		$this->hooks[] = 'scm_generate_snapshots' ;

		require_once $gfconfig.'plugins/scmgit/config.php' ;

		$this->default_git_server = $default_git_server ;
		if (isset ($git_root)) {
			$this->git_root = $git_root;
		} else {
			$this->git_root = $GLOBALS['sys_chroot'].'/scmrepos/git' ;
		}

		$this->register () ;
	}

	function getDefaultServer() {
		return $this->default_git_server ;
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
                        echo ' (Git: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
                }
        }

	function getBlurb () {
		return _('<p>Documentation for Git is available <a href="http://git-scm.com/">here</a>.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Git Access</b></p><p>This project\'s Git repository can be checked out through anonymous access with the following command.</p>');
		$b .= '<p>' ;
		$b .= '<tt>git clone '.util_make_url ('/anonscm/git/'.$project->getUnixName().'/'.$project->getUnixName().'.git').'</tt><br />';
		$b .= '</p>';

		$result = db_query_params ('SELECT u.user_id, u.user_name, u.realname FROM plugin_scmgit_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2',
					   array ($project->getID(),
						  'A')) ;
		$rows = db_numrows ($result) ;

		if ($rows > 0) {
			$b .= ngettext ('<p><b>Developer\'s repository</b></p><p>One of this project\'s members also has a personal Git repository that can be checked out anonymously.</p>',
					'<p><b>Developers\' repositories</b></p><p>Some of this project\'s members also have personal Git repositories that can be checked out anonymously.</p>',
				$rows);
			$b .= '<p>' ;
			for ($i=0; $i<$rows; $i++) {
				$user_id = db_result($result,$i,'user_id');
				$user_name = db_result($result,$i,'user_name');
				$real_name = db_result($result,$i,'realname');
				$b .= '<tt>git clone '.util_make_url ('/anonscm/git/'.$project->getUnixName().'/users/'.$user_name.'.git').'</tt> ('.util_make_link_u ($user_name, $user_id, $real_name).')<br />';
			}
			$b .= '</p>';
		}

		return $b ;
	}

	function getInstructionsForRW ($project) {
		if (session_loggedin()) {
			$u =& user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b = _('<p><b>Developer GIT Access via SSH</b></p><p>Only project developers can access the GIT tree via this method. SSH must be installed on your client machine. Enter your site password when prompted.</p>');
			$b .= '<p><tt>git clone git+ssh://'.$d.'@' . $project->getSCMBox() . $this->git_root .'/'. $project->getUnixName() .'/'. $project->getUnixName() .'.git</tt></p>' ;
		} else {
			$b = _('<p><b>Developer GIT Access via SSH</b></p><p>Only project developers can access the GIT tree via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper value. Enter your site password when prompted.</p>');
		$b .= '<p><tt>git clone git+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . $this->git_root .'/'. $project->getUnixName() .'/'. $project->getUnixName() .'.git</tt></p>' ;

		if (session_logged_in()) {
                        $u =& user_get_object(user_getid()) ;
			if ($u->getUnixStatus() == 'A') {
				$result = db_query_params ('SELECT * FROM plugin_scmgit_personal_repos p WHERE p.group_id=$1 AND p.user_id=$2',
							   array ($project->getID(),
								  $u->getID())) ;
				if ($result && db_numrows ($result) > 0) {
					$b .= _('<p><b>Access to your private repository</b></p><p>You have a private repository for this project, accessible through SSH with the following method. Enter your site password when prompted.</p>');
					$b .= '<p><tt>git clone git+ssh://'.$u->getUnixName().'@' . $project->getSCMBox() . $this->git_root .'/'. $project->getUnixName() .'/users/'. $u->getUnixName() .'.git</tt></p>' ;
				} else {
				}
			}
		}

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

	function printBrowserPage ($params) {
		global $HTML;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin ($this->name)) {
			if ($this->browserDisplayable ($project)) {
				print '<iframe src="'.util_make_url ("/plugins/scmgit/cgi-bin/gitweb.cgi?p=".$project->getUnixName().'/'.$project->getUnixName().'.git').'" frameborder="no" width=100% height=700></iframe>' ;
			}
		}
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Git Repository Browser'));
		$b .= _('<p>Browsing the Git tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
				      _('Browse Git Repository')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

// 	function getStatsBlock ($project) {
// 		global $HTML ;
// 		$b = '' ;

// 		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
// 					  array ($project->getID()));
		
// 		if (db_numrows($result) > 0) {
// 			$b .= $HTML->boxMiddle(_('Repository Statistics'));

// 			$tableHeaders = array(
// 				_('Name'),
// 				_('Adds'),
// 				_('Commits')
// 				);
// 			$b .= $HTML->listTableTop($tableHeaders);
			
// 			$i = 0;
// 			$total = array('adds' => 0, 'commits' => 0);
			
// 			while($data = db_fetch_array($result)) {
// 				$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
// 				$b .= '<td width="50%">' ;
// 				$b .= util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
// 				$b .= '</td><td width="25%" align="right">'.$data['adds']. '</td>'.
// 					'<td width="25%" align="right">'.$data['commits'].'</td></tr>';
// 				$total['adds'] += $data['adds'];
// 				$total['commits'] += $data['commits'];
// 				$i++;
// 			}
// 			$b .= '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
// 			$b .= '<td width="50%"><strong>'._('Total').':</strong></td>'.
// 				'<td width="25%" align="right"><strong>'.$total['adds']. '</strong></td>'.
// 				'<td width="25%" align="right"><strong>'.$total['commits'].'</strong></td>';
// 			$b .= '</tr>';
// 			$b .= $HTML->listTableBottom();
// 		}

// 		return $b ;
// 	}
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

		$project_name = $project->getUnixName() ;
		$root = $this->git_root . '/' . $project_name ;
		$unix_group = 'scm_' . $project_name ;
                system ("mkdir -p $root") ;

		$main_repo = $root . '/' .  $project_name . '.git' ;
		if (!is_file ("$main_repo/HEAD") && !is_dir("$main_repo/objects") && !is_dir("$main_repo/refs")) {
			system ("GIT_DIR=\"$main_repo\" git init --bare --shared=group") ;
			system ("GIT_DIR=\"$main_repo\" git update-server-info") ;
			if (is_file ("$main_repo/hooks/post-update.sample")) {
				rename ("$main_repo/hooks/post-update.sample",
					"$main_repo/hooks/post-update") ;
			}
			if (!is_file ("$main_repo/hooks/post-update")) {
				$f = fopen ("$main_repo/hooks/post-update") ;
				fwrite ($f, "exec git-update-server-info\n") ;
				fclose ($f) ;
			}
			if (is_file ("$main_repo/hooks/post-update")) {
				system ("chmod +x $main_repo/hooks/post-update") ;
			}
			system ("echo \"Git repository for $project_name\" > $main_repo/description") ;
			system ("find $main_repo -type d | xargs chmod g+s") ;
		}
		system ("chgrp -R $unix_group $root") ;
		system ("chmod g+s $root") ;
		if ($project->enableAnonSCM()) {
			system ("chmod g+wX,o+rX-w $root") ;
			system ("chmod -R g+wX,o+rX-w $main_repo") ;
		} else {
			system ("chmod g+wX,o-rwx $root") ;
			system ("chmod -R g+wX,o-rwx $main_repo") ;
		}

		$result = db_query_params ('SELECT u.user_name FROM plugin_scmgit_personal_repos p, users u WHERE p.group_id=$1 AND u.user_id=p.user_id AND u.unix_status=$2',
					   array ($project->getID(),
						  'A')) ;
		$rows = db_numrows ($result) ;
		for ($i=0; $i<$rows; $i++) {
			system ("mkdir -p $root/users") ;
			$user_name = db_result($result,$i,'user_name');
			$repodir = $root . '/users/' .  $user_name . '.git' ;

			if (!is_file ("$repodir/HEAD") && !is_dir("$repodir/objects") && !is_dir("$repodir/refs")) {
				system ("git clone --bare $main_repo $repodir") ;
				system ("GIT_DIR=\"$repodir\" git update-server-info") ;
				system ("echo \"Git repository for user $owner in project $project_name\" > $repodir/description") ;
				system ("chown -R $user_name:$unix_group $repodir") ;
			}
			if ($project->enableAnonSCM()) {
				system ("chmod -R g+rX-w,o+rX-w $repodir") ;
			} else {
				system ("chmod -R g+rX-w,o-rwx $repodir") ;
			}			
		}
	}

	function updateRepositoryList ($params) {
		$groups = $this->getGroups () ;
		$list = array () ;
		foreach ($groups as $project) {
			if ($this->browserDisplayable ($project)) {
				$list[] = $project ;
			}
		}

		$config_dir = '/etc/gforge/plugins/scmgit' ;
		$fname = $config_dir . '/gitweb.conf' ;
		$config_f = fopen ($fname.'.new', 'w') ;
		$rootdir = $this->git_root;
		fwrite($config_f, "\$projectroot = '$rootdir';\n");
		fwrite($config_f, "\$projects_list = '$config_dir/gitweb.list';\n");
		fwrite($config_f, "@git_base_url_list = ('". util_make_url ('/anonscm/git') . "');\n");
		fwrite($config_f, "\$logo = '". util_make_url ('/plugins/scmgit/gitweb/git-logo.png') . "';\n");
		fwrite($config_f, "\$favicon = '". util_make_url ('/plugins/scmgit/gitweb/git-favicon.png')."';\n");
		fwrite($config_f, "\$stylesheet = '". util_make_url ('/plugins/scmgit/gitweb/gitweb.css')."';\n");
		fwrite($config_f, "\$prevent_xss = 'true';\n");
		fclose($config_f);
		chmod ($fname.'.new', 0644) ;
		rename ($fname.'.new', $fname) ;

		$fname = $config_dir . '/gitweb.list' ;

		$f = fopen ($fname.'.new', 'w') ;
		foreach ($list as $project) {
                        $repos = $this->getRepositories($rootdir . "/" .  $project->getUnixName());
                        foreach ($repos as $repo) {
                                $reldir = substr($repo, strlen($rootdir) + 1);
			        fwrite ($f, $reldir . "\n");
                        }
		}
		fclose ($f) ;
		chmod ($fname.'.new', 0644) ;
		rename ($fname.'.new', $fname) ;
	}

        function getRepositories($path) {
                if (! is_dir($path))
                        return;
                $list = Array();
                $entries = scandir($path);
                foreach ($entries as $entry) {
                        $fullname = $path . "/" . $entry;
                        if (($entry == ".") or ($entry == ".."))
                                continue;
                        if (is_dir($fullname)) {
                                if (is_link($fullname))
                                        continue;
                                $result = $this->getRepositories($fullname);
                                $list = array_merge($list, $result);
                        } else if ($entry == "HEAD") {
                                $list[] = $path;
                        }
                }
                return $list;
        }

	function generateSnapshots ($params) {
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
			unlink ($tarball) ;
			return false;
		}

                // TODO: ideally we generate one snapshot per git repository
		$toprepo = $this->git_root ;
		$repo = $toprepo . '/' . $project->getUnixName() . '/' .  $project->getUnixName() . '.git' ;

		if (!is_dir ($repo)) {
			unlink ($tarball) ;
			return false ;
		}

		$today = date ('Y-m-d') ;
		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}

		system ("GIT_DIR=\"$repo\" git archive --format=tar --prefix=$group_name-scm-$today/ HEAD | gzip > $tmp/snapshot.tar.gz");
		chmod ("$tmp/snapshot.tar.gz", 0644) ;
		copy ("$tmp/snapshot.tar.gz", $snapshot) ;
		unlink ("$tmp/snapshot.tar.gz") ;

		system ("tar czCf $toprepo $tmp/tarball.tar.gz " . $project->getUnixName()) ;
		chmod ("$tmp/tarball.tar.gz", 0644) ;
		copy ("$tmp/tarball.tar.gz", $tarball) ;
		unlink ("$tmp/tarball.tar.gz") ;
		system ("rm -rf $tmp") ;
	}

	function gatherStats ($params) {
		global $last_user, $usr_adds, $usr_deletes,
		$usr_updates, $updates, $adds;
		
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

			$usr_adds    = array () ;
			$usr_updates = array () ;
			$usr_deletes = array () ;

			$adds    = 0 ;
			$updates = 0 ;

			$repo = $this->git_root . '/' . $project->getUnixName() . '/' . $project->getUnixName() . '.git';
			if (!is_dir ($repo) || !is_dir ("$repo/refs")) {
				// echo "No repository\n" ;
				db_rollback () ;
				return false ;
			}

			$pipe = popen ("GIT_DIR=\"$repo\" git log --since=@$start_time --until=@$end_time --all --pretty='format:%n%an <%ae>' --name-status", 'r' ) ;

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

			$last_user    = "";
			while (!feof($pipe) && $data = fgets ($pipe)) {
				$line = trim($data);
				if (strlen($line) > 0) {
					$result = preg_match("/^(?<name>.+) <(?<mail>.+)>/", $line, $matches);
					if ($result) {
						// Author line
						$last_user = $matches['name'];
					} else {
						// Short-commit stats line
						preg_match("/^(?<mode>[AM])\s+(?<file>.+)$/", $line, $matches);
						if ($last_user == "") continue;
						if ($matches['mode'] == 'A') {
							$usr_adds[$last_user]++;
							$adds++;
						} elseif ($matches['mode'] == 'M') {
							$usr_updates[$last_user]++;
							$updates++;
						} elseif ($matches['mode'] == 'D') {
							$usr_deletes[$last_user]++;
						}
					}
				}
			}

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
					
				$uu = $usr_updates[$user] ? $usr_updates[$user] : 0 ;
				$ua = $usr_adds[$user] ? $usr_adds[$user] : 0 ;
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
		}
		db_commit();
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
