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
		$b .= _('<p><b>Developer Bazaar Access via SSH</b></p><p>Only project developers can access the Bazaar branches via this method. SSH must be installed on your client machine. Substitute <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
		$b .= '<p><tt>bzr checkout bzr+ssh://<i>'._('developername').'</i>@' . $project->getSCMBox() . $this->bzr_root .'/'. $project->getUnixName().'/'._('branchname').'</tt></p>' ;
		
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
		$code = 0 ;
		$branch = '' ;
		foreach ($this->main_branch_names as $bname) {
			system ("bzr ls file://$repo/$bname > /dev/null 2>&1", $code) ;
			if ($code == 0) {
				$branch = $bname ;
				break ;
			}
		}
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
