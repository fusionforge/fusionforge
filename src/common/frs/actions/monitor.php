<?php
/**
 * FusionForge FRS: Monitor Action
 *
 * Copyright 2014, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

/* please do not add require here : use www/frs/index.php to add require */
/* global variables used */
global $group_id; // id of group
global $g; // group object
global $HTML;

$sysdebug_enable = false;

$ajax = getIntFromRequest('ajax', 1);
$package_id = getIntFromRequest('package_id');

$result = array();
if (!forge_check_perm('frs', $package_id, 'read')) {
	$warning_msg = _('FRS Action Denied.');
	if ($ajax) {
		$result['html'] = $HTML->warning_msg($warning_msg);
		echo json_encode($result);
		exit;
	} else {
		session_redirect('/frs/?group_id='.$group_id);
	}
}

$redirect_url = getStringFromRequest('redirect_url', '/my/');

if ($package_id) {
	$frsp = frspackage_get_object($package_id);
	if (!$frsp || !is_object($frsp)) {
		$error_msg = _('Error Getting FRSPackage');
		if ($ajax) {
			$result['html'] = $HTML->error_msg($error_msg);
			echo json_encode($result);
			exit;
		} else {
			session_redirect('/frs/?group_id='.$group_id);
		}
	} elseif ($frsp->isError()) {
		$error_msg = $frsp->getErrorMessage();
		if ($ajax) {
			$result['html'] = $HTML->error_msg($error_msg);
			echo json_encode($result);
			exit;
		} else {
			session_redirect('/frs/?group_id='.$group_id);
		}
	}
	$monitorStatus = getIntFromRequest('status');
	$url = '/frs/?group_id='.$frsp->Group->getID().'&package_id='.$package_id.'&action=monitor';
	if ($monitorStatus) {
		if ($frsp->setMonitor()) {
			if ($ajax) {
				$url .= '&status=0';
				$result['html'] = $HTML->feedback(_('Monitoring started successfuly'));
				$result['action'] = 'javascript:controllerFRS.doAction({action:\''.$url.'\', id:\'pkgid'.$package_id.'\'})';
				$result['property'] = 'onclick';
				$result['img'] = $HTML->getStopMonitoringPic($frsp->getName().' - '._('Stop monitoring this package'));
				echo json_encode($result);
				exit;
			} else {
				session_redirect($redirect_url);
			}
		} else {
			$error_msg = $frsp->getErrorMessage();
			if ($ajax) {
				$result['html'] = $HTML->error_msg($error_msg);
				echo json_encode($result);
				exit;
			} else {
				session_redirect($redirect_url);
			}
		}
	} else {
		if ($frsp->stopMonitor()) {
			if ($ajax) {
				$url .= '&status=1';
				$result['html'] = $HTML->feedback(_('Monitoring stopped successfuly'));
				$result['action'] = 'javascript:controllerFRS.doAction({action:\''.$url.'\', id:\'pkgid'.$package_id.'\'})';
				$result['property'] = 'onclick';
				$result['img'] = $HTML->getStartMonitoringPic($frsp->getName().' - '._('Start monitoring this package'));
				echo json_encode($result);
				exit;
			} else {
				session_redirect($redirect_url);
			}
		} else {
			$error_msg = $frsp->getErrorMessage();
			if ($ajax) {
				$result['html'] = $HTML->error_msg($error_msg);
				echo json_encode($result);
				exit;
			} else {
				session_redirect($redirect_url);
			}
		}
	}
}
$error_msg = _('Missing package_id');
if ($ajax) {
	$result['html'] = $HTML->error_msg($error_msg);
	echo json_encode($result);
	exit;
} else {
	session_redirect('/frs/?group_id='.$group_id);
}
