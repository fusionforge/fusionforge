<?php
/**
 * GForge FRS Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id$
 */

require_once('../env.inc.php');
require_once('pre.php');
require_once('common/frs/FRSPackage.class');
require_once('www/frs/include/frs_utils.php');


if (session_loggedin()) {
	$group_id = getIntFromRequest('group_id');
	$filemodule_id = getIntFromRequest('filemodule_id');
	$start = getIntFromRequest('start');
	$stop = getIntFromRequest('stop');
	
	if ($group_id && $filemodule_id) {
		//
		//  Set up local objects
		//
		$g =& group_get_object($group_id);
		if (!$g || !is_object($g) || $g->isError()) {
			exit_no_group();
		}

		$f=new FRSPackage($g,$filemodule_id);
		if (!$f || !is_object($f)) {
			exit_error('Error','Error Getting FRSPackage');
		} elseif ($f->isError()) {
			exit_error('Error',$f->getErrorMessage());
		}

		if ($stop) {
			if (!$f->stopMonitor()) {
				exit_error(_('Error'),$f->getErrorMessage());
			} else {
				frs_header(array('title'=>_('Monitoring stopped'),'group'=>$group_id));
				echo _('Monitoring Has Been Stopped');
				frs_footer();
			}
		} elseif($start) {
			if (!$f->setMonitor()) {
				exit_error('Error',$f->getErrorMessage());
			} else {
				frs_header(array('title'=>_('Monitoring started'),'group'=>$group_id));
				echo _('Monitoring Has Been Started');
				frs_footer();
			}
		}
	} else {
		exit_missing_param();
	}

} else {
	exit_not_logged_in();
}

?>
