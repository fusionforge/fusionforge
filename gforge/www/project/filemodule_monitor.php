<?php
/**
 * GForge FRS Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: forum_utils.php.patched,v 1.1.2.1 2002/11/30 09:57:57 cbayle Exp $
 */

require_once('pre.php');
require_once('common/frs/FRSPackage.class');


if (session_loggedin()) {
	if ($filemodule_id && $group_id) {
		//
		//  Set up local objects
		//
		$g =& group_get_object($group_id);
		if (!$g || !is_object($g) || $g->isError()) {
			exit_no_group();
		}

		$f=new FRSPackage($g,$filemodule_id);
		if (!$f || !is_object($f)) {
			exit_error('Error','Error Getting Forum');
		} elseif ($f->isError()) {
			exit_error('Error',$f->getErrorMessage());
		}

		if ($stop) {
			if (!$f->stopMonitor()) {
				exit_error('Error',$f->getErrorMessage());
			} else {
				exit_error('Monitoring Stopped','Monitoring Has Been Stopped');
			}
		} elseif($start) {
			if (!$f->setMonitor()) {
				exit_error('Error',$f->getErrorMessage());
			} else {
				exit_error('Monitoring Started','Monitoring Has Been Started');
			}
		}
	} else {
		exit_missing_param();
	}

} else {
	exit_not_logged_in();
}

?>
