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
		$this->SCMPlugin () ;
		$this->name = 'scmcvs';
		$this->text = 'CVS';
		$this->hooks[] = 'scm_snapshots_and_tarballs' ;

		require_once $GLOBALS['gfconfig'].'plugins/scmcvs/config.php' ;

		$this->default_cvs_server = $default_cvs_server ;
		if ($cvs_root) {
			$this->cvs_root = $cvs_root;
		} else {
			$this->cvs_root = "/cvsroot";
		} 
		//$this->default_cvs_server = $default_cvs_server ;
		//$this->this_server = $this_server ;
		$this->enabled_by_default = $enabled_by_default ;

		$this->register () ;
	}
	
	function getDefaultServer() {
		return $this->default_cvs_server;
	}

	function CallHook ($hookname, $params) {
		global $HTML;
		
		switch ($hookname) {
		case 'scm_snapshots_and_tarballs':
			$this->generateSnapshots ($params) ;
			break;
		default:
			parent::CallHook ($hookname, $params) ;
		}
	}

	function getPage ($params) {
		global $HTML ;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin($this->name)) {
		
			print _('Some CVS documentation is available <a href="http://www.nongnu.org/cvs/">Here</a>');

			$cvsrootend=$project->getSCMBox().':/cvsroot/'.$project->getUnixName();
			$cvsrootend = $project->getSCMBox().':'.$this->cvs_root.'/'.$project->getUnixName();

			// Table for summary info
			print '<table width="100%"><tr valign="top"><td width="65%">' ;

			// Anonymous CVS Instructions
			if ($project->enableAnonSCM()){
				echo _('<p><b>Anonymous CVS Access</b></p><p>This project\'s CVS repository can be checked out through anonymous (pserver) CVS with the following instruction set. The module you wish to check out must be specified as the <i>modulename</i>. When prompted for a password for <i>anonymous</i>, simply press the Enter key.</p>');
				print '
						<p>
						<tt>cvs -d :pserver:anonymous@' . $cvsrootend.' login</tt><br/>
						<tt>cvs -d :pserver:anonymous@' . $cvsrootend.' checkout <em>'._('modulename').'</em></tt>
						</p>';
			}
			
			// Developer Access
			if (session_loggedin ()) {
				echo _('<p><b>Developer CVS Access via SSH</b></p><p>Only project developers can access the CVS tree via this method. SSH must be installed on your client machine. Substitute <i>modulename</i> with the proper value. Enter your site password when prompted.</p>');
				print '
					<p>
					<tt>export CVS_RSH=ssh</tt><br/>
					<tt>cvs -d :ext:' ;
				$u = session_get_user();
				print $u->getUnixName();
				print '@'.$cvsrootend.' checkout <em>'._('modulename').'</em></tt>
					</p>';
			} else {
				echo _('<p><b>Developer CVS Access via SSH</b></p><p>Only project developers can access the CVS tree via this method. SSH must be installed on your client machine. Substitute <i>modulename</i> and <i>developername</i> with the proper values. Enter your site password when prompted.</p>');
				print '
					<p>
					<tt>export CVS_RSH=ssh</tt><br/>
					<tt>cvs -d :ext:<em>'._('developername').'</em>@'.$cvsrootend.' checkout <em>'._('modulename').'</em></tt>
					</p>';
			}
			
			// CVS Snapshot
			if ($this->browserDisplayable ($project)) {
				print '<p>[' ;
				print util_make_link ("/snapshots.php?group_id=".$project->getID(),
						      _('Download The Nightly CVS Tree Snapshot')
					) ;
				print ']</p>';
			}
			print '</td><td width="35%" valign="top">' ;
			
			// CVS Browsing 
			echo $HTML->boxTop(_('Repository History'));
			echo $this->getDetailedStats(array('group_id'=>$project->getID())).'<p>';
			if ($this->browserDisplayable ($project)) {
				echo _('<b>Browse the CVS Tree</b><p>Browsing the CVS tree gives you a great view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
				echo '<p>[' ;
				echo util_make_link ("/scm/viewvc.php/?root=".$project->getUnixName(),
						     _('Browse CVS Repository')
					) ;
				echo ']</p>' ;
				$hook_params['project_name'] = $project->getUnixName();
				plugin_hook ("cvs_stats", $hook_params) ;
			}
			echo $HTML->boxBottom();
			print '</td></tr></table>' ;
		}	
	}

	function adminUpdate ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin($this->name)) {
			if (array_key_exists('scmcvs_enable_anoncvs', $params)){
				$project->SetUsesAnonSCM(true);
			} else {
				$project->SetUsesAnonSCM(false);
			}
			if (array_key_exists('scmcvs_enable_pserver', $params)){
				$project->SetUsesPserver(true);
			} else {
				$project->SetUsesPserver(false);
			}
		}
	}
	
	function getAdminPage ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin($this->name)) {
			print '<p>';
			if ($project->isPublic()) {
				print '<input type="checkbox" name="scmcvs_enable_anoncvs" value="1" '.$this->c($project->enableAnonSCM()).'/><strong>'._('Enable Anonymous Access').'</strong><br />';
			} else {
				print '<input type="checkbox" name="scmcvs_enable_anoncvs" value="1" '.$this->c($project->enableAnonSCM()).' DISABLED/>'._('Enable Anonymous Access').' <strong>'._("You project is private and so, you can't turn Anonymous Access on").'</strong><br />';

			}
			print '<input type="checkbox" name="scmcvs_enable_pserver" value="1" '.$this->c($project->enablePserver()).' /><strong>'._('Enable pserver').'</strong></p>' ;
		}
	}

	function getStats ($params) {
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
	
	function getDetailedStats ($params) {
		global $HTML;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$result = db_query_params('SELECT u.realname, u.user_name, u.user_id, sum(commits) as commits, sum(adds) as adds, sum(adds+commits) as combined FROM stats_cvs_user s, users u WHERE group_id=$1 AND s.user_id=u.user_id AND (commits>0 OR adds >0) GROUP BY u.user_id, realname, user_name, u.user_id ORDER BY combined DESC, realname',
					  array ($project->getID()));
		
		if (db_numrows($result) > 0) {
			$tableHeaders = array(
				_('Name'),
				_('Adds'),
				_('Commits')
				);
			echo $HTML->listTableTop($tableHeaders);
			
			$i = 0;
			$total = array('adds' => 0, 'commits' => 0);
			
			while($data = db_fetch_array($result)) {
				echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
				echo '<td width="50%">' ;
				echo util_make_link_u ($data['user_name'], $data['user_id'], $data['realname']) ;
				echo '</td><td width="25%" align="right">'.$data['adds']. '</td>'.
					'<td width="25%" align="right">'.$data['commits'].'</td></tr>';
				$total['adds'] += $data['adds'];
				$total['commits'] += $data['commits'];
				$i++;
			}
			echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>';
			echo '<td width="50%"><strong>'._('Total').':</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['adds']. '</strong></td>'.
				'<td width="25%" align="right"><strong>'.$total['commits'].'</strong></td>';
			echo '</tr>';
			echo $HTML->listTableBottom();
			echo '<hr size="1" />';
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

	function generateSnapshots ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		$group_name = $project->getUnixName();

		$snapshot = $sys_scm_snapshots_path.'/'.$group_name.'-scm-latest.tar.gz';
		$tarball = $sys_scm_tarballs_path.'/'.$group_name.'-scmroot.tar.gz';

		if (! $project->usesPlugin ($this->name)
		    || ! $project->enableAnonSCM()) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false;
		}

		$repo = $this->cvs_root . '/' . $project->getUnixName() ;

		$repo_exists = false ;
		if (is_dir ($repo) && is_dir ("$repo/CVSROOT")) {
			$repo_exists = true ;
		}
		
		if (!$repo_exists) {
			unlink ($snapshot) ;
			unlink ($tarball) ;
			return false ;
		}

		/*
		 $compression = --gzip
		 
		 Déléguer la suite à un script shell

		 system ("scmcvs-snapshots.sh $repo $group_name $snapshot")
		 
		 tmp=$(mktemp -d)
		 cd $tmp
		 $today=$(date +%Y-%m-%d)
		 mkdir -p $group_name-$today
		 cd $group_name-$today
		 cvs -d $repo checkout .
		 cd ..
		 tar cf snapshot.tar.compressed $compression $group_name-$today
		 chmod
		 mv snapshot.tar.compressed $snapshot
		 cd /
		 rm -rf $tmp
		*/
	}
  }

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
