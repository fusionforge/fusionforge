<?php
/**
 * GForge SCM Library
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2005-04-16
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function scm_header($params) {
	global $DOCUMENT_ROOT, $HTML, $Language, $sys_use_cvs;
	if (!$sys_use_cvs) {
		exit_disabled();
	}

	$project =& group_get_object($params['group']);
	if (!$project || !is_object($project)) {
		exit_error('Error','Could Not Get Project');
	} elseif ($project->isError()) {
		exit_error('Error',$project->getErrorMessage());
	}

	if (!$project->usesCVS()) {
		exit_error('Error',$Language->getText('scm_index','error_this_project_has_turned_off'));
	}
	site_project_header(array('title'=>$Language->getText('scm_index','cvs_repository'),'group'=>$params['group'],'toptab'=>'scm',));
	/*
		Show horizontal links
	*/
	echo '<span style="font-family:arial, helvetica">';
	echo '<p><strong>';
	echo '<a href="/scm/?group_id='.$params['group'].'">' .$Language->getText('scm_index','title').'</a>
		 | <a href="/scm/admin/?group_id='.$params['group'].'">' .
		$Language->getText('project_admin','scm_admin').'</a></strong></p></span>';
	echo '<p>&nbsp;</p>';
}

function scm_footer() {
	site_project_footer(array());
}

?>
