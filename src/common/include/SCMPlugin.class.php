<?php
/**
 * FusionForge source control management
 *
 * Copyright 2004-2009, Roland Mas
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/scm.php';

abstract class SCMPlugin extends Plugin {
	/**
	 * SCMPlugin() - constructor
	 *
	 */
	function SCMPlugin () {
		$this->Plugin() ;
		$this->hooks[] = 'scm_plugin';
		$this->hooks[] = 'scm_page';
		$this->hooks[] = 'scm_admin_page';
		$this->hooks[] = 'scm_admin_update';
 		$this->hooks[] = 'scm_stats';
		$this->hooks[] = 'scm_create_repo';
		# Other common hooks that can be enabled per plugin:
		# scm_generate_snapshots
		# scm_gather_stats
		# scm_browser_page
		# scm_update_repolist
	}

	function CallHook ($hookname, &$params) {
		global $HTML ;

		switch ($hookname) {
		case 'scm_plugin':
			$scm_plugins=& $params['scm_plugins'];
			$scm_plugins[]=$this->name;
			break;
		case 'scm_page':
			$this->printPage ($params) ;
			break ;
		case 'scm_browser_page':
			$this->printBrowserPage ($params) ;
			break ;
		case 'scm_admin_page':
			$this->printAdminPage ($params) ;
			break ;
		case 'scm_admin_update':
			$this->adminUpdate($params);
			break ;
		case 'scm_stats':
			$this->printShortStats ($params) ;
			break;
		case 'scm_create_repo':
			session_set_admin();
			$this->createOrUpdateRepo($params);
			break;
		case 'scm_update_repolist':
			session_set_admin () ;
			$this->updateRepositoryList ($params) ;
			break;
		case 'scm_generate_snapshots': // Optional
			session_set_admin () ;
			$this->generateSnapshots ($params) ;
			break;
		case 'scm_gather_stats': // Optional
			session_set_admin () ;
			$this->gatherStats ($params) ;
			break;
		default:
			// Forgot something
		}
	}

	final function register () {
		global $scm_list ;

		$scm_list[] = $this->name ;
	}

	function browserDisplayable($project) {
		if ($project->usesSCM()
		    && $project->usesPlugin($this->name)
		    && $project->enableAnonSCM()) {
			return true;
		} else {
			return false;
		}
	}

	abstract function createOrUpdateRepo ($params) ;

	function printShortStats ($params) {
		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		if ($project->usesPlugin ($this->name)) {
			echo ' ('.$this->text.')' ;
		}
	}

	function getBlurb () {
		return '<p>' . _('Unimplemented SCM plugin.') . '</p>';
	}

	function getInstructionsForAnon ($project) {
		return '<p>' . _('Instructions for anonymous access for unimplemented SCM plugin.') . '</p>';
	}

	function getInstructionsForRW ($project) {
		return '<p>' . _('Instructions for read-write access for unimplemented SCM plugin.') . '</p>';
	}

	function getSnapshotPara ($project) {
		return '<p>' . _('Instructions for snapshot access for unimplemented SCM plugin.') . '</p>';
	}

	function getBrowserLinkBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Repository Browser'));
		$b .= '<p>';
		$b .= _('Browsing the SCM tree is not yet implemented for this SCM plugin.');
		$b .= '</p>';
		$b .= '<p>[' ;
		$b .= util_make_link ("/scm/?group_id=".$project->getID(),
				      _('Not implemented yet')
			) ;
		$b .= ']</p>' ;
		return $b ;
	}

	function getBrowserBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Repository Browser'));
		$b .= '<p>';
		$b .= _('Browsing the SCM tree is not yet implemented for this SCM plugin.');
		$b .= '</p>';
		return $b ;
	}

	function getStatsBlock ($project) {
		global $HTML ;
		$b = $HTML->boxMiddle(_('Repository Statistics'));
		$b .= '<p>';
		$b .= _('Not implemented for this SCM plugin yet.') ;
		$b .= '</p>';
		return $b ;
	}

	function printPage ($params) {
		global $HTML;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		if ($project->usesPlugin ($this->name)) {

			// Table for summary info
			print '<table width="100%"><tr valign="top"><td width="65%">'."\n" ;
			print $this->getBlurb ()."\n" ;

			// Instructions for anonymous access
			if ($project->enableAnonSCM()) {
				print $this->getInstructionsForAnon ($project) ;
			}

			// Instructions for developer access
			print $this->getInstructionsForRW ($project) ;

			// Snapshot
			if ($this->browserDisplayable ($project)) {
				print $this->getSnapshotPara ($project) ;
			}
			print '</td>'."\n".'<td width="35%" valign="top">'."\n" ;

			// Browsing
			echo $HTML->boxTop(_('Repository History'));
			echo _('Data about current and past states of the repository') ;
			if ($this->browserDisplayable($project)) {
				echo $this->getStatsBlock($project);
				echo $this->getBrowserLinkBlock($project);
			}

			echo $HTML->boxBottom();
			print '</td></tr></table>' ;
		}
	}

	function printBrowserPage($params) {
		global $HTML;

		$project = $this->checkParams ($params) ;
		if (!$project) {
			return false ;
		}

		if ($project->usesPlugin ($this->name)) {
			if ($this->browserDisplayable ($project)) {
				// print '<iframe src="'.util_make_url('/scm/browser.php?title='.$group->getUnixName()).'" frameborder="0" width=100% height=700></iframe>' ;
			}
		}
	}

	function printAdminPage($params) {
		$group = group_get_object($params['group_id']);
		$ra = RoleAnonymous::getInstance() ;

		if ( $group->usesPlugin ( $this->name ) && $ra->hasPermission('project_read', $group->getID())) {
			print '<p><input type="checkbox" name="scm_enable_anonymous" value="1" '.$this->c($group->enableAnonSCM()).' /><strong>'._('Enable Anonymous Read Access').'</strong></p>';
		}
	}

	function adminUpdate($params) {
		$project = $this->checkParams($params);
		if (!$project) {
			return false;
		}

		if ($project->usesPlugin($this->name) ) {
			if (isset($params['scm_enable_anonymous']) && $params['scm_enable_anonymous']) {
				$project->SetUsesAnonSCM(true);
			} else {
				$project->SetUsesAnonSCM(false);
			}
		}
	}

	function checkParams ($params) {
		$group_id = $params['group_id'] ;
		$project = group_get_object($group_id);
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
