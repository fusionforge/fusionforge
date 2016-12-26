<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2016, Franck Villaume - TrivialDev
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
require_once $gfcommon.'widget/Widget.class.php';

$request =& HTTPRequest::instance();

$lm = new WidgetLayoutManager();
$good = false;
$redirect   = '/';
$owner = $request->get('owner');

if ($owner) {
	$owner_id   = (int)substr($owner, 1);
	$owner_type = substr($owner, 0, 1);
	switch($owner_type) {
		case WidgetLayoutManager::OWNER_TYPE_USER:
			$owner_id = user_getid();
			$layout_id =(int)$request->get('layout_id');
			$redirect = '/my/';
			$good = true;
			break;
		case WidgetLayoutManager::OWNER_TYPE_GROUP:
			$pm = ProjectManager::instance();
			if ($project = $pm->getProject($owner_id)) {
				$group_id = $owner_id;
				$_REQUEST['group_id'] = $_GET['group_id'] = $group_id;
				$request->params['group_id'] = $group_id; //bad!
				$redirect = '/projects/'. $project->getUnixName().'/';
				if (!forge_check_perm('project_admin', $group_id) &&
					!forge_check_global_perm('forge_admin')) {
					$GLOBALS['Response']->redirect($redirect);
				}
				$good = true;
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_HOME:
			$redirect = '/';
			if (!forge_check_global_perm('forge_admin')) {
				$GLOBALS['Response']->redirect($redirect);
			}
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
		default:
			break;
	}
	if ($good) {
		if (($layout_id = (int)$request->get('layout_id')) || $request->get('action') == 'preferences') {
			$name = null;
			if ($request->exist('name')) {
				$param = $request->get('name');
				$name = array_pop(array_keys($param));
			}
			$instance_id = (int)$param[$name];
			switch($request->get('action')) {
				case 'widget':
					if ($name && $request->exist('layout_id')) {
						if ($widget = Widget::getInstance($name)) {
							if ($widget->isAvailable()) {
								$action = array_pop(array_keys($param[$name]));
								switch($action) {
									case 'remove':
										$instance_id = (int)$param[$name][$action];
										if (($owner_type == WidgetLayoutManager::OWNER_TYPE_GROUP) && (forge_check_perm ('project_admin', $owner_id, NULL))) {
												$lm->removeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id, $widget);
										} elseif (($owner_type == WidgetLayoutManager::OWNER_TYPE_HOME) && (forge_check_global_perm('forge_admin'))) {
												$lm->removeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id, $widget);
										} elseif (($owner_type == WidgetLayoutManager::OWNER_TYPE_TRACKER) && (forge_check_perm('tracker_admin', $owner_id))) {
												$lm->removeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id, $widget);
										} else {
											$lm->removeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id, $widget);
										}
										break;
									case 'add':
									default:
										$category = str_replace(' ', '_', $widget->getCategory());
										$redirect ='/widgets/widgets.php?owner='. $owner_type.$owner_id.'&layout_id='. $layout_id.'#filter-widget-categ-'.$category;
										$lm->addWidget($owner_id, $owner_type, $layout_id, $name, $widget, $request);
										break;
								}
							}
						}
					}
					break;
				case 'minimize':
					if ($name) {
						$lm->mimizeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id);
					}
					break;
				case 'maximize':
					if ($name) {
						$lm->maximizeWidget($owner_id, $owner_type, $layout_id, $name, $instance_id);
					}
					break;
				case 'preferences':
					if ($name) {
						$lm->displayWidgetPreferences($owner_id, $owner_type, $layout_id, $name, $instance_id);
					}
					break;
				case 'layout':
					$lm->updateLayout($owner_id, $owner_type, $request->get('layout_id'), $request->get('new_layout'));
					break;
				default:
					$lm->reorderLayout($owner_id, $owner_type, $layout_id, $request);
					break;
			}
		}
	}
}
if (!$request->isAjax()) {
	session_redirect($redirect, false);
}
