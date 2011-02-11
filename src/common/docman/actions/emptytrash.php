<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * http://fusionforge.org
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; //group object
global $dirid; //id of doc_group
global $group_id; // id of group

if ( !forge_check_perm('docman', $group_id, 'approve')) {
	$return_msg = _('Document Manager Action Denied.');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}

/* you must first delete files before dirs because of database constraints */
$emptyFile = db_query_params('DELETE FROM doc_data WHERE stateid=$1 and group_id=$2',array('2',$group_id));
if (!$emptyFile)
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode(db_error()));

$emptyDir = db_query_params('DELETE FROM doc_groups WHERE stateid=$1 and group_id=$2',array('2',$group_id));
if (!$emptyDir)
	session_redirect('/docman/?group_id='.$group_id.'&error_msg='.urlencode(db_error()));

$return_msg = _('Emptied Trash successfully.');
session_redirect('/docman/?group_id='.$group_id.'&view=trashfile&feedback='.urlencode($return_msg));
?>
