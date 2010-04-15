<?php
/** FusionForge Bazaar plugin
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

		require_once $gfconfig.'plugins/scmbzr/config.php' ;
		
		$this->default_bzr_server = $default_bzr_server ;
		if (isset ($bzr_root)) {
			$this->bzr_root = $bzr_root;
		} else {
			$this->bzr_root = $GLOBALS['sys_chroot'].'/scmrepos/bzr' ;
		}

		$this->main_branch_names = array () ;
		$this->main_branch_names[] = 'trunk' ;
		$this->main_branch_names[] = 'master' ;
		$this->main_branch_names[] = 'main' ;
		$this->main_branch_names[] = 'head' ;
		$this->main_branch_names[] = 'HEAD' ;
		
		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_bzr_server ;
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
			echo ' (Bazaar: '.sprintf(_('<strong>%1$s</strong> commits, <strong>%2$s</strong> adds'), number_format($commit_num, 0), number_format($add_num, 0)).")";
		}
	}
	
	function getBlurb () {
		return _('<p>Documentation for Bazaar (sometimes referred to as "bzr") is available <a href="http://bazaar-vcs.org/Documentation">here</a>.</p>') ;
	}

	function getInstructionsForAnon ($project) {
		$b =  _('<p><b>Anonymous Bazaar Access</b></p><p>This project\'s Bazaar repository can be checked out through anonymous access with the following command.</p>');
		$b .= '<p>' ;
		$b .= '<tt>bzr checkout '.util_make_url ('/anonscm/bzr/'.$project->getUnixName().'/').'</tt><br />';
		$b .= '</p>';
		return $b ;
	}

	function getInstructionsForRW ($project) {
		$b = '' ;
		if (session_loggedin()) {
			$u =& user_get_object(user_getid()) ;
			$d = $u->getUnixName() ;
			$b .= _('<p><b>Developer Bazaar Access via SSH</b></p><p>Only project developers can access the Bazaar branches via this method. SSH must be installed on your client machine. Enter your site password when prompted.</p>');
			$b .= '<p><tt>bzr checkout bzr+ssh://'.$d.'@' . $project->getSCMBox() . $this->bzr_root .'/'. $project->getUnixName().'/'._('branchname').'</tt></p>' ;
		} else {
			$b .= _('<p><b>Developer Bazaar Access via SSH</b></p><p>Only project developers can access the Bazaar branches via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper value. Enter your site password when prompted.</p>');
			$b .= '<p><tt>bzr checkout bzr+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . $this->bzr_root .'/'. $project->getUnixName().'/'._('branchname').'</tt></p>' ;
		}
		return $b ;
	}

	function getSnapshotPara ($project) {
		return ;
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Bazaar Repository Browser'));
		$b .= _('<p>Browsing the Bazaar tree gives you a view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/browser.php?group_id=".$project->getID(),
				      _('Browse Bazaar Repository')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

	function getStatsBlock ($project) {
		return ;
	}

        function printBrowserPage ($params) {
                global $HTML;

                $project = $this->checkParams ($params) ;
                if (!$project) {
                        return false ;
                }
                
                if ($project->usesPlugin ($this->name)) {
                        if ($this->browserDisplayable ($project)) {
                                print '<iframe src="'.util_make_url ("/scm/loggerhead/".$project->getUnixName()).'" frameborder="no" width=100% height=700></iframe>' ;
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

		$repo = $this->bzr_root . '/' . $project->getUnixName() ;
		$unix_group = 'scm_' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo)) {
			$pipe = popen ("bzr info $repo 2>/dev/null", "r") ;
			$line = fgets ($pipe) ;
			fclose ($pipe) ;
		
			if (preg_match ("/^Shared repository/", $line) != 0) {
				$repo_exists = true ;
			}
		}
		
		if (!$repo_exists) {
			system ("mkdir -p $repo") ;
			system ("bzr init-repo --no-trees $repo >/dev/null") ;
			system ("find $repo -type d | xargs chmod g+s") ;
		}

		system ("chgrp -R $unix_group $repo") ;
		if ($project->enableAnonSCM()) {
			system ("chmod -R g+wX,o+rX-w $repo") ;
		} else {
			system ("chmod -R g+wX,o-rwx $repo") ;
		}
	}

        function updateRepositoryList ($params) {
                $groups = $this->getGroups () ;

		$dir = '/var/lib/gforge/plugins/scmbzr/public-repositories' ;

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
			symlink ($this->bzr_root . '/' . $create, $dir . '/' . $create) ;
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
			$usr_updates = array () ;
			$usr_adds = array () ;

			$toprepo = $this->bzr_root ;
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
					}
				}
			}
			if ($curdate == $date) {
				$adds = $adds + $curadds ;
				$updates = $updates + $curupdates ;
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

	function findMainBranch ($project) {
		$toprepo = $this->bzr_root ;
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

		$snapshot = forge_get_config('scm_snapshots_path').'/'.$group_name.'-scm-latest.tar.gz';
		$tarball = forge_get_config('scm_tarballs_path').'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)) {
			return false;
		}

		if (! $project->enableAnonSCM()) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false;
		}

		$toprepo = $this->bzr_root ;
		$repo = $toprepo . '/' . $project->getUnixName() ;

		if (!is_dir ($repo) || !is_file ("$repo/format")) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false ;
		}

		$tmp = trim (`mktemp -d`) ;
		if ($tmp == '') {
			return false ;
		}
		$today = date ('Y-m-d') ;
		$branch = $this->findMainBranch ($project) ;
		if ($branch != '') {
			system ("bzr export --root=$group_name-scm-$today $tmp/snapshot.tar.gz $repo/$bname") ;
			chmod ("$tmp/snapshot.tar.gz", 0644) ;
			copy ("$tmp/snapshot.tar.gz", $snapshot) ;
			unlink ("$tmp/snapshot.tar.gz") ;
			system ("rm -rf $tmp/$dir") ;
		} else {
			unlink ($snapshot) ;
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
