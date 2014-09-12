<?php
/**
 * FusionForge Bazaar plugin
 *
 * Copyright 2009, Roland Mas
 * Copyright 2013-2014, Franck Villaume - TrivialDev
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

forge_define_config_item ('default_server', 'scmbzr', forge_get_config ('web_host')) ;
forge_define_config_item ('repos_path', 'scmbzr', forge_get_config('chroot').'/scmrepos/bzr') ;

class BzrPlugin extends SCMPlugin {
	function BzrPlugin () {
		global $gfconfig;
		$this->SCMPlugin () ;
		$this->name = 'scmbzr';
		$this->text = 'Bazaar';
		$this->hooks[] = 'scm_generate_snapshots' ;
                $this->hooks[] = 'scm_browser_page';
                $this->hooks[] = 'scm_update_repolist' ;
                $this->hooks[] = 'scm_gather_stats' ;

		$this->main_branch_names = array () ;
		$this->main_branch_names[] = 'trunk' ;
		$this->main_branch_names[] = 'master' ;
		$this->main_branch_names[] = 'main' ;
		$this->main_branch_names[] = 'head' ;
		$this->main_branch_names[] = 'HEAD' ;

		$this->register () ;
	}

	function getDefaultServer() {
		return forge_get_config('default_server', 'scmbzr') ;
	}

	function printShortStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return;
		}

		if ($project->usesPlugin($this->name) && forge_check_perm('scm', $project->getID(), 'read')) {
			$result = db_query_params('SELECT sum(updates) AS updates, sum(adds) AS adds FROM stats_cvs_group WHERE group_id=$1',
						  array ($project->getID())) ;
			$commit_num = db_result($result,0,'updates');
			$add_num    = db_result($result,0,'adds');
			if (!$commit_num) {
				$commit_num=0;
			}
			if (!$add_num) {
				$add_num=0;
			}
			echo ' (Bazaar: '.sprintf(_('<strong>%1$s</strong> updates, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}

	function getBlurb () {
		return '<p>'
				. sprintf(_('Documentation for %1$s is available at <a href="%2$s">%2$s</a>.'),
							'Bazaar (“bzr”)',
							'http://bazaar-vcs.org/Documentation')
				. '</p>';
	}

	function getInstructionsForAnon ($project) {
		$b = '<h2>';
		$b .=  _('Anonymous Bazaar Access');
		$b = '</h2>';
		$b .= '<p>';
		$b .= _('This project\'s Bazaar repository can be checked out through anonymous access with the following command.');
		$b .= '</p>';
		$b .= '<p>' ;
		$b .= '<tt>bzr checkout '.util_make_url ('/anonscm/bzr/'.$project->getUnixName().'/').'</tt><br />';
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = '' ;
		if (session_loggedin()) {
			$u = user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b .= '<h2>';
			$b .= sprintf(_('Developer %s Access via SSH'), 'Bazaar');
			$b .= '</h2>';
			$b .= '<p>';
			$b .= sprintf(_('Only project developers can access the %s tree via this method.'), 'Bazaar');
			$b .= ' ';
			$b .= _('SSH must be installed on your client machine.');
			$b .= ' ';
			$b .= _('Enter your site password when prompted.');
			$b .= '</p>';
			$b .= '<p><tt>bzr checkout bzr+ssh://'.$d.'@' . $this->getBoxForProject($project) . forge_get_config('repos_path', 'scmbzr') .'/'. $project->getUnixName().'/'._('branchname').'</tt></p>' ;
		} else {
			$b .= '<h2>';
			$b .= sprintf(_('Developer %s Access via SSH'), 'Bazaar');
			$b .= '</h2>';
			$b .= '<p>';
			$b .= sprintf(_('Only project developers can access the %s tree via this method.'), 'Bazaar');
			$b .= ' ';
			$b .= _('SSH must be installed on your client machine.');
			$b .= ' ';
			$b .= _('Substitute <em>developername</em> with the proper value.');
			$b .= ' ';
			$b .= _('Enter your site password when prompted.');
			$b .= '</p>';
			$b .= '<p><tt>bzr checkout bzr+ssh://<i>'._('developername').'</i>@' . $this->getBoxForProject($project) . forge_get_config('repos_path', 'scmbzr') .'/'. $project->getUnixName().'/'._('branchname').'</tt></p>' ;
		}
		return $b ;
	}

	function getSnapshotPara ($project) {
		return;
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(sprintf(_('%s Repository Browser'), 'Bazaar'));
		$b .= '<p>';
		$b .= sprintf(_("Browsing the %s tree gives you a view into the current status of this project's code."), 'Bazaar');
		$b .= ' ';
		$b .= _('You may also view the complete histories of any file in the repository.');
		$b .= '</p>';
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
								sprintf(_('Browse %s Repository'), 'Bazaar')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

	function getStatsBlock ($project) {
		return;
	}

	function printBrowserPage ($params) {
		global $HTML;
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return;
		}

		if ($project->usesPlugin ($this->name)) {
			if ($this->browserDisplayable ($project)) {
				print '<iframe id="scm_iframe" src="'.util_make_url ("/scm/loggerhead/".$project->getUnixName()).'" frameborder="0" width="100%" ></iframe>' ;
				html_use_jqueryautoheight();
				echo $HTML->getJavascripts();
				echo '<script type="text/javascript">//<![CDATA[
					jQuery(\'#scm_iframe\').iframeAutoHeight({heightOffset: 50});
					//]]></script>';
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

		$project_name = $project->getUnixName();

		$repo = forge_get_config('repos_path', 'scmbzr') . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project_name ;

		$repo_exists = false ;
		if (is_dir ($repo)) {
			$pipe = popen ("bzr info $repo 2>/dev/null", "r") ;
			$line = fgets ($pipe) ;
			fclose ($pipe) ;

			if (preg_match ("/^Shared repository/", $line) != 0
			    || preg_match ("/^Repository branch/", $line) != 0) {
				$repo_exists = true ;
			}
		}

		if (!$repo_exists) {
			$tmp_repo = util_mkdtemp('.bzr', $project_name);
			if ($tmp_repo == false) {
				return false;
			}

			system ("bzr init-repo --no-trees $tmp_repo >/dev/null") ;
			system ("find $tmp_repo/.bzr -type d | xargs chmod g+s") ;
			system ("chmod -R g+wX,o+rX-w $tmp_repo/.bzr") ;
			system ("chgrp $unix_group $tmp_repo/.bzr") ;

			system ("mkdir -p $repo") ;
			system ("chgrp $unix_group $repo") ;
			system ("chmod g+s $repo") ;
			system ("mv $tmp_repo/.bzr $repo/.bzr");
			rmdir ($tmp_repo);
		}

		if ($project->enableAnonSCM()) {
			system ("chmod o+rX-w $repo") ;
		} else {
			system ("chmod o-rwx $repo") ;
		}
	}

        function updateRepositoryList ($params) {
                $groups = $this->getGroups () ;

		$dir = forge_get_config('data_path').'/plugins/scmbzr/public-repositories' ;

		if (!is_dir($dir)) {
			mkdir ($dir, 0644, true);
		}

		$oldlist = array () ;
		$dh = opendir ($dir) ;
		while (($file = readdir($dh)) !== false) {
			if ($file != '.' && $file != '..') {
				$oldlist[] = $file ;
			}
		}
		closedir($dh) ;
		sort ($oldlist) ;

                $newlist = array () ;
                foreach ($groups as $project) {
                        if ($this->browserDisplayable ($project)) {
                                $newlist[] = $project->getUnixName() ;
                        }
                }
		sort ($newlist) ;

		$dellist = array () ;
		$createlist = array () ;

		while (count ($oldlist) > 0 && count ($newlist) > 0) {
			$o = $oldlist[0] ;
			$n = $newlist[0] ;
			if ($o > $n) {
				$createlist[] = array_shift ($newlist) ;
			} elseif ($o < $n) {
				$dellist[] = array_shift ($oldlist) ;
			} else {
				array_shift ($newlist) ;
				array_shift ($oldlist) ;
			}
		}
		$dellist = array_merge ($dellist, $oldlist) ;
		$createlist = array_merge ($createlist, $newlist) ;

		foreach ($dellist as $del) {
			unlink ($dir . '/' . $del) ;
		}
		foreach ($createlist as $create) {
			symlink (forge_get_config('repos_path', 'scmbzr') . '/' . $create, $dir . '/' . $create) ;
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
                        $start_time = gmmktime( 0, 0, 0, $month, $day, $year);
                        $end_time = $start_time + 86400;

			$date = sprintf ("%04d-%02d-%02d", $year, $month, $day);

                        $updates = 0 ;
                        $adds = 0 ;
			$usr_adds = array () ;
			$usr_updates = array () ;
			$usr_deletes = array () ;
			$usr_commits = array () ;

			$toprepo = forge_get_config('repos_path', 'scmbzr') ;
			$repo = $toprepo . '/' . $project->getUnixName() ;

			$branch = $this->findMainBranch ($project) ;

			if ($branch == '') {
				db_rollback () ;
				return false ;
			}

                        $pipe = popen ("bzr log file://$repo/$branch --long --verbose 2> /dev/null", 'r' ) ;

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

                        // Analyzing history stream
			$sep = '------------------------------------------------------------' ;
			$currev = '' ;
			$curuser = '' ;
			$curdate = '' ;
			$state = '' ;
			$curadds = 0 ;
			$curupdates = 0 ;
			$curcommits = 0 ;
			$curdeletes = 0 ;
                        while (! feof ($pipe) &&
                               $line = rtrim (fgets ($pipe))) {
				if ($line == $sep) {
					if ($curdate == $date) {
						$adds = $adds + $curadds ;
						$updates = $updates + $curupdates ;
					}
					if ($curdate != '' && $curdate < $date) {
						fclose ($pipe) ;
						break ;
					}
					$currev = '' ;
					$curuser = '' ;
					$curdate = '' ;
					$state = '' ;
					$curadds = 0 ;
					$curupdates = 0 ;
				} elseif (preg_match( '/^revno: ([0-9]+)$/', $line, $matches)) {
					$currev = $matches[1] ;
				} elseif (preg_match( '/^committer: (.*)$/', $line, $matches)) {
					$curuser = $matches[1] ;
				} elseif (preg_match( '/^timestamp: ... (\d\d\d\d-\d\d-\d\d)/', $line, $matches)) {
					$curdate = $matches[1] ;
				} elseif (preg_match( '/^modified:/', $line, $matches)) {
					$state = 'modified' ;
				} elseif (preg_match( '/^renamed:/', $line, $matches)) {
					$state = 'renamed' ;
				} elseif (preg_match( '/^removed:/', $line, $matches)) {
					$state = 'removed' ;
				} elseif (preg_match( '/^added/', $line, $matches)) {
					$state = 'added' ;
				} else {
					switch ($state) {
					case 'modified':
						$curupdates++ ;
						break ;
					case 'added':
						$curadds++ ;
						break ;
					case 'deletes':
						$curdeletes++ ;
						break ;
					}
				}
				$curcommits++ ;
			}
			if ($curdate == $date) {
				$adds = $adds + $curadds ;
				$updates = $updates + $curupdates ;
				$deletes = $deletes + $curdeletes ;
				$commits = $commits + $curcommits ;
			}

                        // inserting group results in stats_cvs_groups
			if ($updates > 0 || $adds > 0 || $deletes > 0 || $commits > 0) {
				if (!db_query_params ('INSERT INTO stats_cvs_group (month,day,group_id,checkouts,commits,adds,updates,deletes) VALUES ($1,$2,$3,$4,$5,$6)',
						      array ($month_string,
							     $day,
							     $project->getID(),
							     0,
							     $commits,
							     $adds,
							     $updates,
							     $deletes))) {
					echo "Error while inserting into stats_cvs_group\n" ;
					db_rollback () ;
					return false ;
				}
			}

                        // building the user list
                        $user_list = array_unique( array_merge( array_keys( $usr_adds ), array_keys( $usr_updates ), array_keys( $usr_commits ) ) );

                        foreach ( $user_list as $user ) {
                                // trying to get user id from user name
                                $u = user_get_object_by_name ($user) ;
                                if ($u) {
                                        $user_id = $u->getID();
                                } else {
                                        continue;
                                }

				$uc = $usr_commits[$user] ? $usr_commits[$user] : 0 ;
				$uu = $usr_updates[$user] ? $usr_updates[$user] : 0 ;
				$ua = $usr_adds[$user] ? $usr_adds[$user] : 0 ;
				$ud = $usr_deletess[$user] ? $usr_deletes[$user] : 0 ;
				if ($uu > 0 || $ua > 0 || $uc > 0 || $ud > 0) {
					if (!db_query_params ('INSERT INTO stats_cvs_user (month,day,group_id,user_id,commits,adds,updates,deletes) VALUES ($1,$2,$3,$4,$5,$6)',
							      array ($month_string,
								     $day,
								     $project->getID(),
								     $user_id,
								     $uc,
								     $ua,
								     $uu,
								     $ud))) {
						echo "Error while inserting into stats_cvs_user\n" ;
						db_rollback () ;
						return false ;
					}
				}
                        }
                }
                db_commit();
        }

	function findMainBranch ($project) {
		$toprepo = forge_get_config('repos_path', 'scmbzr') ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		$branch = '' ;

		foreach ($this->main_branch_names as $bname) {
			system ("bzr ls file://$repo/$bname > /dev/null 2>&1", $code) ;
			if ($code == 0) {
				$branch = $bname ;
				break ;
			}
		}
		return $branch;
	}

	function generateSnapshots ($params) {

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
			if (file_exists ($snapshot)) unlink ($snapshot) ;
			if (file_exists ($tarball)) unlink ($tarball) ;
			return false;
		}

		$toprepo = forge_get_config('repos_path', 'scmbzr') ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo) || !is_file ("$repo/format")) {
			if (file_exists ($snapshot)) unlink ($snapshot) ;
			if (file_exists ($tarball)) unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		$today = date ('Y-m-d') ;
		$branch = $this->findMainBranch ($project) ;
		if ($branch != '') {
			system ("bzr export --root=$group_name-scm-$today --format=tar - $repo/$bname |".forge_get_config('compression_method')."> $tmp/snapshot") ;
			chmod ("$tmp/snapshot", 0644) ;
			copy ("$tmp/snapshot", $snapshot) ;
			unlink ("$tmp/snapshot") ;
			system ("rm -rf $tmp/$dir") ;
		} else {
			unlink ($snapshot) ;
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
