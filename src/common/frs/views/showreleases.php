<?php
/**
 * Project Admin: Edit Releases of Packages
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010 (c), FusionForge Team
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
global $HTML; // html object
global $group_id; // id of group
global $g; // group object
global $warning_msg; // warning message

$package_id = getIntFromRequest('package_id');

if (!$package_id) {
	$warning_msg = _('Choose a package to be edited.');
	session_redirect('/frs/?view=admin&group_id='.$group_id);
}

session_require_perm('frs', $group_id, 'write');

$frsp = new FRSPackage($g, $package_id);
if (!$frsp || !is_object($frsp)) {
	exit_error(_('Could Not Get FRS Package'),'frs');
} elseif ($frsp->isError()) {
	exit_error($frsp->getErrorMessage(),'frs');
}

$rs = $frsp->getReleases();
if (count($rs) < 1) {
	exit_error(_('No Releases Of This Package Are Available'), 'frs');
}

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerFRS;
jQuery(document).ready(function() {
	controllerFRS = new FRSController();
});
//]]>
<?php
echo html_ac(html_ap() - 1);

// Display a list of releases in this package
echo html_e('h2', array(), _('Available Releases for the package').' '.$frsp->getName());

$title_arr=array(_('Release Name'), _('Date'), _('Actions'));

echo $HTML->listTableTop($title_arr);
for ($i = 0; $i < count($rs); $i++) {
	$cells = array();
	$cells[][] = $rs[$i]->getName();
	$cells[][] = date('Y-m-d H:i',$rs[$i]->getReleaseDate());
	$deleteUrlAction = util_make_uri('/frs/?action=deleterelease&package_id='.$package_id.'&group_id='.$group_id.'&release_id='.$rs[$i]->getID());
	$cells[][] = util_make_link('/frs/?view=editrelease&group_id='.$group_id.'&package_id='.$package_id.'&release_id='.$rs[$i]->getID(), '['._('Edit').']')
			.util_make_link('#', $HTML->getDeletePic(_('Delete this release'), _('Delete release')), array('onclick' => 'javascript:controllerFRS.toggleConfirmBox({idconfirmbox: \'confirmbox1\', do: \''._('Delete the release').' '.$rs[$i]->getName().'\', cancel: \''._('Cancel').'\', height: 150, width: 300, action: \''.$deleteUrlAction.'\'})' ), true);
	echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($i, true)), $cells);
}
echo $HTML->listTableBottom();
echo $HTML->jQueryUIconfirmBox('confirmbox1', _('Delete release'), _('You are about to delete permanently this release. Are you sure? This action is definitive.'));
