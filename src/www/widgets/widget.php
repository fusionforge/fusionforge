<?php
/**
 * http://fusionforge.org
 * Copyright 2016,2018, Franck Villaume - TrivialDev
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
require_once $gfcommon.'widget/Widget.class.php';
require_once $gfcommon.'widget/Valid_Widget.class.php';

$lm = new WidgetLayoutManager();

$request =& HTTPRequest::instance();
$good = false;
$redirect   = '/';
$vOwner = new Valid_Widget_Owner('owner');
$vOwner->required();
if ($request->valid($vOwner)) {
	$owner = $request->get('owner');
	$owner_id   = (int)substr($owner, 1);
	$owner_type = substr($owner, 0, 1);
	switch($owner_type) {
		case WidgetLayoutManager::OWNER_TYPE_USER:
			if ($owner_id == user_getid()) {
				$redirect = '/my/';
				$good = true;
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_GROUP:
			$pm = ProjectManager::instance();
			if ($project = $pm->getProject($owner_id)) {
				$group_id = $owner_id;
				$_REQUEST['group_id'] = $_GET['group_id'] = $group_id;
				$request->params['group_id'] = $group_id; //bad!
				$redirect = '/projects/'. $project->getUnixName();
				$good = true;
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_HOME:
			$good = true;
			break;
		case WidgetLayoutManager::OWNER_TYPE_TRACKER:
			if ($at = artifactType_get_object($owner_id)) {
				$_REQUEST['group_id'] = $_GET['group_id'] = $at->Group->getID();
				$request->params['group_id'] = $at->Group->getID(); //bad!
				$redirect = '/tracker/?group_id='.$at->Group->getID().'&atid='.$at->getID();
				if ((strlen($request->get('func')) > 0)) {
					$redirect .= '&func='.$request->get('func');
					if ($request->get('aid')) {
						$redirect .= '&aid='.$request->get('aid');
					}
				}
				if (!forge_check_global_perm('forge_admin') && !forge_check_perm('tracker_admin', $at->getID())) {
					$GLOBALS['Response']->redirect($redirect);
				}
				$good = true;
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_USERHOME:
			if ($owner_id == user_getid()) {
				$user = user_get_object(user_getid());
				$redirect = '/users/'.$user->getUnixName();
				$good = true;
			}
		default:
			break;
	}
	if ($good) {
		if ($request->exist('name')) {
			$param = $request->get('name');
			$v = array_keys($param);
			$name = array_pop($v);
			$instance_id = (int)$param[$name];
			if ($widget =& Widget::getInstance($name, $owner_id)) {
				if ($widget->isAvailable()) {
					switch ($request->get('action')) {
						case 'rss':
							$widget->displayRss();
							exit();
							break;
						case 'update':
							if ($layout_id = (int)$request->get('layout_id')) {
								if ($owner_type == WidgetLayoutManager::OWNER_TYPE_USER ||
									forge_check_perm('project_admin', $group_id) ||
									forge_check_global_perm('forge_admin') ||
									forge_check_perm('tracker_admin', $owner_id)) {
									if ($request->get('cancel') || $widget->updatePreferences($request)) {
										$lm->hideWidgetPreferences($owner_id, $owner_type, $layout_id, $name, $instance_id);
									}
								}
							}
							break;
						case 'ajax':
							if ($widget->isAjax()) {
								header("Cache-Control: no-store, no-cache, must-revalidate");
								sysdebug_ajaxbody();
								$widget->loadContent($instance_id);
								echo $widget->getContent();
								exit();
							}
							break;
						case 'iframe':
							echo '<html><head>';
							$GLOBALS['HTML']->displayStylesheetElements();
							echo '</head><body class="main_body_row contenttable">';
							$widget->loadContent($instance_id);
							echo $widget->getContent();
							echo '</body></html>';
							exit;
							break;
						case 'process':
							$widget->loadContent($instance_id);
							$widget->process($owner_type, $owner_id);
							exit;
						default:
							break;
					}
				}
			}
		}
	}
}
if (!$request->isAjax()) {
	session_redirect($redirect);
}
