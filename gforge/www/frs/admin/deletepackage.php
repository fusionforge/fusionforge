<?php
/**
 *
 * Project Admin: Edit Packages
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

require_once('pre.php');	
require_once('www/frs/include/frs_utils.php');
require_once('common/frs/FRSPackage.class');

if (!$group_id) {
	exit_no_group();
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

/*

	Relatively simple form to delete packages of releases

*/

frs_admin_header(array('title'=>$Language->getText('project_admin_editpackages','title'),'group'=>$group_id));

	echo '<strong>'.$frsp->getName().'</strong><p>';
	echo '
	<form action="/frs/admin/?group_id='.$group_id.'" method="post">
	<input type="hidden" name="func" value="delete_package" />
	<input type="hidden" name="package_id" value="'. $package_id .'" />
	'.$Language->getText('frs_admin','delete_package_warning').'
	<p>
	<input type="checkbox" name="sure" value="1">'.$Language->getText('frs_admin','sure').'<br />
	<input type="checkbox" name="really_sure" value="1">'.$Language->getText('frs_admin','really_sure').'<br />
	<input type="submit" name="submit" value="'.$Language->getText('frs_admin','delete').'" />
	</form>';

frs_admin_footer();

?>
