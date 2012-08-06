<?php
/**
 * FRS Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c) FusionForge Team
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

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'frs/FRSPackage.class.php';
require_once $gfwww.'frs/include/frs_utils.php';


if (session_loggedin()) {
	$group_id = getIntFromRequest('group_id');
	$filemodule_id = getIntFromRequest('filemodule_id');
	$start = getIntFromRequest('start');
	$stop = getIntFromRequest('stop');

	if ($group_id && $filemodule_id) {
		//
		//  Set up local objects
		//
		$g = group_get_object($group_id);
		if (!$g || !is_object($g) || $g->isError()) {
			exit_no_group();
		}

		$f=new FRSPackage($g,$filemodule_id);
		if (!$f || !is_object($f)) {
			exit_error(_('Error Getting FRSPackage'),'frs');
		} elseif ($f->isError()) {
			exit_error($f->getErrorMessage(),'frs');
		}

		if ($stop) {
			if (!$f->stopMonitor()) {
				exit_error($f->getErrorMessage(),'frs');
			} else {
                $feedback = _('Monitoring Has Been Stopped');
				frs_header(array('title'=>_('Monitoring stopped'),'group'=>$group_id));
				frs_footer();
			}
		} elseif($start) {
			if (!$f->setMonitor()) {
				exit_error($f->getErrorMessage(),'frs');
			} else {
                $feedback = _('Monitoring Has Been Started');
				frs_header(array('title'=>_('Monitoring started'),'group'=>$group_id));
				frs_footer();
			}
		}
	} else {
		exit_missing_param('',array(_('Project ID'),_('File Module ID')),'frs');
	}
} else {
	exit_not_logged_in();
}

?>
