<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010, Franck Villaume - Capgemini
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

$groupname = getStringFromRequest('groupname');
$parent_dirid = getIntFromRequest('parent_dirid');

$dg = new DocumentGroup($g, $dirid);
if ($dg->isError())
	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&error_msg='.urlencode($dg->getErrorMessage()));

if (!$dg->update($groupname, $parent_dirid))
	session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&error_msg='.urlencode($dg->getErrorMessage()));

$return_msg = _('Document Directory Updated successfully.');
session_redirect('/docman/?group_id='.$group_id.'&view=listfile&dirid='.$dirid.'&feedback='.urlencode($return_msg));
?>
