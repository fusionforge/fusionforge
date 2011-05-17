<?php
/**
 *
 * Project Admin: Edit Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
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

require_once('../../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'frs/include/frs_utils.php';
require_once $gfcommon.'frs/FRSPackage.class.php';

$group_id = getIntFromRequest('group_id');
$package_id = getIntFromRequest('package_id');
if (!$group_id) {
	exit_no_group();
}

$project = group_get_object($group_id);
if (!$project || !is_object($project)) {
    exit_no_group();
} elseif ($project->isError()) {
	exit_error($project->getErrorMessage(),'frs');
}

session_require_perm ('frs', $group_id, 'write') ;

$frsp = new FRSPackage($project,$package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error(_('Could Not Get FRS Package'),'frs');
} elseif ($frsp->isError()) {
	exit_error($frsp->getErrorMessage(),'frs');
}

/*
	Relatively simple form to delete packages of releases
*/

frs_admin_header(array('title'=>sprintf(_('Delete Package: %1$s'), $frsp->getName()),'group'=>$group_id));

	echo '
	<form action="/frs/admin/?group_id='.$group_id.'" method="post">
	<p>
	<input type="hidden" name="func" value="delete_package" />
	<input type="hidden" name="package_id" value="'. $package_id .'" />
	'._('You are about to permanently and irretrievably delete this package and all its releases and files!').'
        </p> 
        <p> 
	<input type="checkbox" name="sure" value="1" />'._('I\'m Sure').'
        </p> 
        <p> 
	<input type="checkbox" name="really_sure" value="1" />'._('I\'m Really Sure').'
        </p>
        <p>
	<input type="submit" name="submit" value="'._('Delete').'" />
        </p> 
	</form>';

frs_admin_footer();

?>
