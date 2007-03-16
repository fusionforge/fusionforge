<?php
/**
 * Project Admin: Edit Releases of Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
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

/* Updated rewrite of the File Release System to clean up the UI 
 * a little and incorporate FRS.class.		-Darrell
 */

require_once('../../env.inc.php');
require_once('pre.php');	
require_once('www/frs/include/frs_utils.php');
require_once('common/frs/FRSPackage.class');
require_once('common/frs/FRSRelease.class');
require_once('common/frs/FRSFile.class');

$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');
if (!$group_id) {
	exit_no_group();
}
if (!$package_id) {
	header("Location /frs/admin/?group_id=$group_id?feedback=Choose+Package");
	exit;
}

$project =& group_get_object($group_id);
if (!$project || $project->isError()) {
	exit_error('Error',$project->getErrorMessage());
}

$perm =& $project->getPermission(session_get_user());
if (!$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

$frsp = new FRSPackage($project,$package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error('Error','Could Not Get FRS Package');
} elseif ($frsp->isError()) {
	exit_error('Error',$frsp->getErrorMessage());
}

//
//
//
$release_id = getIntFromRequest('release_id');
$func = getStringFromRequest('func');
if ($func=='delete_release' && $release_id) {
	$sure = getStringFromRequest('sure');
	$really_sure = getStringFromRequest('really_sure');

	$frsr = new FRSRelease($frsp,$release_id);
	if (!$frsr || !is_object($frsr)) {
		exit_error('Error','Could Not Get FRS Release');
	} elseif ($frsr->isError()) {
		exit_error('Error',$frsr->getErrorMessage());
	}
	if (!$frsr->delete($sure,$really_sure)) {
		exit_error('Error',$frsr->getErrorMessage());
	} else {
		$feedback .= _('Deleted');
	}
}

/*
	Get the releases of this package
*/
$rs =& $frsp->getReleases();
if (count($rs) < 1) {
	exit_error(_('Error'),_('No Releases Of This Package Are Available'));
}

/*
	Display a list of releases in this package
*/
frs_admin_header(array('title'=>_('Release New File Version'),'group'=>$group_id));

$title_arr=array();
$title_arr[]=_('Package Name');
$title_arr[]=_('Release Name');
$title_arr[]=_('Date');

echo $GLOBALS['HTML']->listTableTop ($title_arr);

for ($i=0; $i<count($rs); $i++) {
	echo '<tr '. $HTML->boxGetAltRowStyle($i) .'>
			<td>'.$frsp->getName().'</td>
			<td><a href="editrelease.php?group_id='.$group_id
				.'&amp;package_id='.$package_id
				.'&amp;release_id='.$rs[$i]->getID().'">'. 
				$rs[$i]->getName().' ['._('Edit').']</a>
				<a href="deleterelease.php?group_id='.$group_id
				.'&amp;package_id='.$package_id
				.'&amp;release_id='.$rs[$i]->getID().'">['._('Delete').']</td><td>'.
				date('Y-m-d H:i',$rs[$i]->getReleaseDate()).'</td></tr>';
}

echo $GLOBALS['HTML']->listTableBottom();

frs_admin_footer(array());

?>
