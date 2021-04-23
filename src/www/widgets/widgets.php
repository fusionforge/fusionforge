<?php
/**
 *
 * Copyright 2011-2014,2016,2018, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/preplugins.php';
require_once $gfcommon.'include/plugins_utils.php';
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';

html_use_jquery();
use_javascript('/widgets/scripts/LayoutController.js');

if (session_loggedin()) {
	$lm = new WidgetLayoutManager();
	$layout_id = getIntFromRequest('layout_id');
	$owner = getStringFromRequest('owner');
	$owner_id   = (int)substr($owner, 1);
	$owner_type = substr($owner, 0, 1);
	switch($owner_type) {
		case WidgetLayoutManager::OWNER_TYPE_USER:
			if ($owner_id == user_getid()) {
				$userm = UserManager::instance();
				$current = $userm->getCurrentUser();
				site_user_header(array('title'=>sprintf(_('Personal Page for %s'), user_getname())));
				$lm->displayAvailableWidgets(user_getid(), WidgetLayoutManager::OWNER_TYPE_USER, $layout_id);
				site_footer();
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_GROUP:
			if ($project = group_get_object($owner_id)) {
				$group_id = $owner_id;
				$_REQUEST['group_id'] = $_GET['group_id'] = $group_id;
				if (forge_check_perm('project_admin', $group_id) || forge_check_global_perm('forge_admin')) {
					$update = getStringFromRequest('update');
					if ('layout' == $update) {
						$title = _("Customize Layout");
					} else {
						$title = _("Add widgets");
					}
					site_project_header(array('title' => $title, 'group' => $group_id, 'toptab' => 'summary'));
					$lm->displayAvailableWidgets($group_id, WidgetLayoutManager::OWNER_TYPE_GROUP, $layout_id);
					site_footer();
				} else {
					session_redirect('/projects/'.$project->getUnixName().'/');
				}
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_HOME:
			if (forge_check_global_perm('forge_admin')) {
				$update = getStringFromRequest('update');
				if ('layout' == $update) {
					$title = _('Customize Layout');
				} else {
					$title = _('Add widgets');
				}
				site_header(array('title' => $title, 'toptab' => 'home'));
				$lm->displayAvailableWidgets(0, WidgetLayoutManager::OWNER_TYPE_HOME, $layout_id);
				site_footer();
			} else {
				session_redirect('/');
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_TRACKER:
			if ($at = artifactType_get_object($owner_id)) {
				use_javascript('/widgets/scripts/WidgetController.js');
				$_REQUEST['group_id'] = $_GET['group_id'] = $at->Group->getID();
				$redirect = '/tracker/?group_id='. $at->Group->getID();
				if (forge_check_global_perm('forge_admin') || forge_check_perm('tracker_admin', $at->getID())) {
					$ath = new ArtifactTypeHtml($at->Group, $at->getID());
					$ath->header(array('atid'=>$ath->getID(), 'title'=>$ath->getName()));
					$lm->displayAvailableWidgets($owner_id, WidgetLayoutManager::OWNER_TYPE_TRACKER, $layout_id);
					$ath->footer();
				} else {
					session_redirect($redirect);
				}
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_USERHOME:
			if ($owner_id == user_getid()) {
				$userm = UserManager::instance();
				$current = $userm->getCurrentUser();
				site_header(array('title'=>sprintf(_('Profile Page for %s'), user_getname())));
				$lm->displayAvailableWidgets(user_getid(), WidgetLayoutManager::OWNER_TYPE_USERHOME, $layout_id);
				site_footer();
			}
			break;
		default:
			break;
	}
} else {
	exit_not_logged_in();
}
