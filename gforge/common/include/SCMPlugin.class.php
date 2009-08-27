<?php
/**
 * FusionForge source control management
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

require_once $gfcommon.'include/scm.php';

abstract class SCMPlugin extends Plugin {
	/**
	 * SCMPlugin() - constructor
	 *
	 */
	function SCMPlugin () {
		$this->Plugin() ;
		$this->hooks[] = 'scm_page';
		$this->hooks[] = 'scm_admin_update';
		$this->hooks[] = 'scm_admin_page';
 		$this->hooks[] = 'scm_stats';
		$this->hooks[] = 'scm_createrepo';
		$this->hooks[] = 'scm_plugin';
	}

	function CallHook ($hookname, $params) {
		global $HTML ;
		
		switch ($hookname) {
		case 'scm_page':
			$this->getPage ($params) ;
			break ;
		case 'scm_admin_update':
			$this->AdminUpdate ($params) ;
			break ;
		case 'scm_admin_page':
			$this->getAdminPage ($params) ;
			break ;
		case 'scm_stats':
			$this->getStats ($params) ;
			break;
		case 'scm_createrepo':
			$this->createOrUpdateRepo ($params) ;
			break;
		case 'scm_plugin':
			$scm_plugins=& $params['scm_plugins'];
			$scm_plugins[]=$this->name;
			break;
		default:
			// Forgot something
		}
	}

	final function register () {
		global $scm_list ;

		$scm_list[] = $this->name ;
	}

	function browserDisplayable ($project) {
		if ($project->usesPlugin($this->name)
		    && $project->enableAnonSCM()) {
			return true ;
		} else {
			return false ;
		}
	}

	function displayBrowser ($project) {
		if ($this->browserDisplayable ($project)) {
			// ...
		} else {
			return '' ;
		}
	}

	abstract function createOrUpdateRepo ($params) ;
	abstract function getStats ($params) ;

	function getBlurb () {
		return _('<p>Unimplemented SCM plugin.</p>');
	}

	function getInstructionsForAnon ($project) {
		return _('<p>Instructions for anonymous access for unimplemented SCM plugin.</p>');
	}

	function getInstructionsForRW ($project) {
		return _('<p>Instructions for read-write access for unimplemented SCM plugin.</p>');
	}

	function getBrowserBlurb () {
		return _('<b>Browse the SCM Tree</b><p>Browsing the SCM tree gives you a great view into the current status of this project\'s code. You may also view the complete histories of any file in the repository.</p>');
	}

	function getPage ($params) {
		global $HTML, $sys_scm_snapshots_path;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin ($this->name)) {

			// Table for summary info
			print '<table width="100%"><tr valign="top"><td width="65%">' ;
			print $this->getBlurb () ;

			// Instructions for anonymous access
			if ($project->enableAnonSCM()) {
				print $this->getInstructionsForAnon ($project) ;
			}
	
			// Instructions for developer access
			print $this->getInstructionsForRW ($project) ;

			// SVN Snapshot
			if ($this->browserDisplayable ($project)) {
				$filename=$project->getUnixName().'-scm-latest.tar.gz';
				if (file_exists($sys_scm_snapshots_path.'/'.$filename)) {
					print '<p>[' ;
					print util_make_link ("/snapshots.php?group_id=".$project->getID(),
							      _('Download the nightly snapshot')
						) ;
					print ']</p>';
				}
			}
			print '</td><td width="35%" valign="top">' ;

			// Browsing
			echo $HTML->boxTop(_('Repository History'));
			echo $this->getDetailedStats(array('group_id'=>$project->getID())).'<p>';
			if ($this->browserDisplayable ($project)) {
				print $this->getBrowserBlurb ($project) ;
				echo '<p>[' ;
				echo util_make_link ("/scm/viewvc.php/?root=".$project->getUnixName(),
						     _('Browse Repository')
					) ;
				echo ']</p>' ;
			}
			
			echo $HTML->boxBottom();
			print '</td></tr></table>' ;
		}
	}

	function AdminUpdate ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}
		
		if ($project->usesPlugin ($this->name) ) {
			if ($params['scm_enable_anonymous']) {
				$project->SetUsesAnonSCM(true);
			} else {
				$project->SetUsesAnonSCM(false);
			}
		}
	}
	
	function getAdminPage ($params) {
		$group =& group_get_object($params['group_id']);
		if ( $group->usesPlugin ( $this->name ) && $group->isPublic()) {
			print '<p><input type="checkbox" name="scm_enable_anonymous" value="1" '.$this->c($group->enableAnonSCM()).' /><strong>'._('Enable Anonymous Access').'</strong></p>';
		}
	}
	
	function checkParams ($params) {
		$group_id = $params['group_id'] ;
		$project =& group_get_object($group_id);
		if (!$project || !is_object($project)) {
			return false;
		} elseif ($project->isError()) {
			return false;
		}
		
		return $project ;
	}
	
	function c($v) {
		if ($v) {
			return 'checked="checked"';
		} else {
			return '';
		}
	}
	
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
