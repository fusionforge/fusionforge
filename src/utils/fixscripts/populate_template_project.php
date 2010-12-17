#! /usr/bin/php
<?php
/**
 * Copyright 2010 Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
			 
$err='';

// Plugins subsystem
require_once('common/include/Plugin.class.php') ;
require_once('common/include/PluginManager.class.php') ;

setup_plugin_manager () ;
session_set_admin () ;

function populateProject($project) {
	db_begin();
	$role = new Role($project);
	$todo = array_keys($role->defaults);
	for ($c=0; $c<count($todo); $c++) {
		if (! ($role_id = $role->createDefault($todo[$c]))) {
			$project->setError(sprintf(_('R%d: %s'),$c,$role->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
		$role = new Role($project, $role_id);
		if ($role->getVal('projectadmin',0)=='A') {
			$role->setUser(session_get_user()->getID());
		}
	}

	if (forge_get_config ('use_tracker')) {
		$ats = new ArtifactTypes($project);
		if (!$ats || !is_object($ats)) {
			$project->setError(_('Error creating ArtifactTypes object'));
			db_rollback();
			setup_gettext_from_context();
			return false;
		} else if ($ats->isError()) {
			$project->setError(sprintf (_('ATS%d: %s'), 1, $ats->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
		if (!$ats->createTrackers()) {
			$project->setError(sprintf (_('ATS%d: %s'), 2, $ats->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
	}

	if (forge_get_config ('use_forum')) {
		$f = new Forum($project);
		if (!$f->create(_('Open-Discussion'),_('General Discussion'),1,'',1,0)) {
			$project->setError(sprintf (_('F%d: %s'), 1, $f->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
		$f = new Forum($project);
		if (!$f->create(_('Help'),_('Get Public Help'),1,'',1,0)) {
			$project->setError(sprintf (_('F%d: %s'), 2, $f->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
		$f = new Forum($project);
		if (!$f->create(_('Developers-Discussion'),_('Project Developer Discussion'),0,'',1,0)) {
			$project->setError(sprintf (_('F%d: %s'), 3, $f->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
	}
               
	if (forge_get_config('use_docman')) {
		$dg = new DocumentGroup($project);
		if (!$dg->create(_('Uncategorized Submissions'))) {
			$project->setError(sprintf(_('DG: %s'),$dg->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
	}

	if (forge_get_config ('use_frs')) {
		$frs = new FRSPackage($project);
		if (!$frs->create("UNIXNAME")) {
			$project->setError(sprintf(_('FRSP: %s'),$frs->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
	}

	if (forge_get_config ('use_pm')) {
		$pg = new ProjectGroup($project);
		if (!$pg->create(_('To Do'),_('Things We Have To Do'),1)) {
			$project->setError(sprintf(_('PG%d: %s'),1,$pg->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
		$pg = new ProjectGroup($project);
		if (!$pg->create(_('Next Release'),_('Items For Our Next Release'),1)) {
			$project->setError(sprintf(_('PG%d: %s'),2,$pg->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
	}

	$ra = RoleAnonymous::getInstance() ;
	$rl = RoleLoggedIn::getInstance() ;
	$ra->linkProject ($project) ;
	$rl->linkProject ($project) ;
	
	$ra->setSetting ('project_read', $project->getID(), 1) ;
	$rl->setSetting ('project_read', $project->getID(), 1) ;
	
	$ra->setSetting ('frs', $project->getID(), 1) ;
	$rl->setSetting ('frs', $project->getID(), 1) ;
	
	$ra->setSetting ('docman', $project->getID(), 1) ;
	$rl->setSetting ('docman', $project->getID(), 1) ;
	
	$ff = new ForumFactory ($project) ;
	foreach ($ff->getAllForumIds() as $fid) {
		$f = forum_get_object ($fid) ;
		if ($f->isPublic()) {
			$l = $f->getModerationLevel() ;
			if ($l == 0) {
				$rl->setSetting ('forum', $fid, 3) ;
			} else {
				$rl->setSetting ('forum', $fid, 2) ;
			}
			if ($f->allowAnonymous()) {
				if ($l == 0) {
					$ra->setSetting ('forum', $fid, 3) ;
				} else {
					$ra->setSetting ('forum', $fid, 2) ;
				}
			} else {
				$ra->setSetting ('forum', $fid, 1) ;
			}
		}
	}
	$pgf = new ProjectGroupFactory ($project) ;
	foreach ($pgf->getAllProjectGroupIds() as $pgid) {
		$pg = projectgroup_get_object ($pgid) ;
		if ($pg->isPublic()) {
			$ra->setSetting ('pm', $pgid, 1) ;
			$rl->setSetting ('pm', $pgid, 1) ;
		}
	}

	$atf = new ArtifactTypeFactory ($project) ;
	foreach ($atf->getAllArtifactTypeIds() as $atid) {
		$at = artifactType_get_object ($atid) ;
		if ($at->isPublic()) {
			$ra->setSetting ('tracker', $atid, 1) ;
			$rl->setSetting ('tracker', $atid, 1) ;
		}
	}

	if (forge_get_config('use_mail')) {
		$mlist = new MailingList($project);
		if (!$mlist->create('commits',_('Commits'),1,session_get_user()->getID())) {
			$project->setError(sprintf(_('ML: %s'),$mlist->getErrorMessage()));
			db_rollback();
			setup_gettext_from_context();
			return false;
		}
	}
	$project->normalizeAllRoles () ;

	db_commit();

	return true;
}
 
$project = group_get_object(5);

if (!populateProject($project)) {
	echo "Error when populating template project!\n";
	exit(1);
}
?>
