<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2016,2018,2021, Franck Villaume - TrivialDev
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

$lm = new WidgetLayoutManager();
$good = false;
$redirect   = '/';
$owner = getStringFromRequest('owner');

if ($owner) {
	$owner_id   = (int)substr($owner, 1);
	$owner_type = substr($owner, 0, 1);
	switch($owner_type) {
		case WidgetLayoutManager::OWNER_TYPE_USER:
			if ($owner_id ==  user_getid()) {
				$layout_id =getIntFromRequest('layout_id');
				$redirect = '/my/';
				$good = true;
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_GROUP:
			if ($project = group_get_object($owner_id)) {
				$group_id = $owner_id;
				$_REQUEST['group_id'] = $_GET['group_id'] = $group_id;
				$redirect = '/projects/'. $project->getUnixName().'/';
				if (!forge_check_perm('project_admin', $group_id) && !forge_check_global_perm('forge_admin')) {
					session_redirect($redirect);
				}
				$good = true;
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_HOME:
			$redirect = '/';
			if (!forge_check_global_perm('forge_admin')) {
				session_redirect($redirect);
			}
			$good = true;
			break;
		case WidgetLayoutManager::OWNER_TYPE_TRACKER:
			if ($at = artifactType_get_object($owner_id)) {
				$_REQUEST['group_id'] = $_GET['group_id'] = $at->Group->getID();
				$redirect = '/tracker/?group_id='.$at->Group->getID().'&atid='.$at->getID();
				$func = getStringFromRequest('func');
				if ((strlen($func) > 0)) {
					$redirect .= '&func='.$func;
					$aid = getIntFromRequest('aid');
					if ($aid) {
						$redirect .= '&aid='.$aid;
					}
				}
				if (!forge_check_global_perm('forge_admin') && !forge_check_perm('tracker_admin', $at->getID())) {
					session_redirect($redirect);
				}
				$good = true;
			}
			break;
		case WidgetLayoutManager::OWNER_TYPE_USERHOME:
			if ($owner_id == user_getid()) {
				$user = user_get_object(user_getid());
				$layout_id = getIntFromRequest('layout_id');
				$redirect = '/users/'.$user->getUnixName();
				$good = true;
			}
			break;
		default:
			break;
	}
	if ($good) {
		$mainaction = getStringFromRequest('action', 'reorder');
		if (($layout_id = getIntFromRequest('layout_id')) || 'preferences' == $mainaction) {
			$name = null;
			$param = getArrayFromRequest('name');
			if (count($param)) {
				$name = array_pop(array_keys($param));
				$instance_id = (int)$param[$name];
			}
			switch($mainaction) {
				case 'widget':
					if ($name) {
						if ($widget = Widget::getInstance($name, $owner_id)) {
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
										$lm->addWidget($owner_id, $owner_type, $layout_id, $name, $widget);
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
					$lm->updateLayout($owner_id, $owner_type, $layout_id, getIntFromRequest('new_layout'));
					break;
				case 'reorder':
				default:
					$lm->reorderLayout($owner_id, $owner_type, $layout_id);
					break;
			}
		}
	}
}
if(!(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtoupper($_SERVER['HTTP_X_REQUESTED_WITH']) == 'XMLHTTPREQUEST')) {
	session_redirect($redirect, false);
}
