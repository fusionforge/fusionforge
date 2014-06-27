<?php
/**
 * FusionForge FRS
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2002-2004 (c) GForge Team
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 * http://fusionforge.org
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

if ( !forge_check_perm('frs', $group_id, 'write')) {
	$warning_msg = _('FRS Access Denied');
	session_redirect('/frs/?group_id='.$group_id);
}

$fpFactory = new FRSPackageFactory($g);
if (!$fpFactory || !is_object($fpFactory)) {
	exit_error(_('Could Not Get FRSPackageFactory'), 'frs');
} elseif ($fpFactory->isError()) {
	exit_error($fpFactory->getErrorMessage(), 'frs');
}

$FRSPackages = $fpFactory->getFRSs();

if (count($FRSPackages) > 0) {
	echo html_e('h2', array(), _('QRS'));
	echo html_e('p', array(), _('Click here to ').util_make_link('/frs/?view=qrs&group_id='.$group_id, _('quick-release a file')));
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

echo html_ao('fieldset');
echo html_e('legend', array(), _('Help about Packages and Releases'));
echo html_e('h3', array(), _('Packages'));
echo html_e('p', array(), _('You can use packages to group different file releases together, or use them however you like.'));
echo html_e('h4', array(), _('An example of packages')._(':'));
echo html_e('p', array(), html_e('strong', array(), 'Mysql-win').html_e('br').html_e('strong', array(), 'Mysql-unix').html_e('br').html_e('strong', array(), 'Mysql-odbc'));
echo html_e('h4', array(), _('Your Packages')._(':'));
echo html_ao('ol');
echo html_e('li', array(), _('Define your packages.'));
echo html_e('li', array(), _('Create new releases of packages.'));
echo html_ac(html_ap() -1);
echo html_e('h3', array(), _('Releases of Packages'));
echo html_e('p', array(), _('A release of a package can contain multiple files.'));
echo html_e('h4', array(), _('Examples of releases')._(':'));
echo html_e('p', array(), html_e('strong', array(), '3.22.1').html_e('br').html_e('strong', array(), '3.22.2').html_e('br').html_e('strong', array(), '3.22.3'));
echo html_e('p', array(), _('You can create new releases of packages by clicking on <strong>Add/Edit Releases</strong> next to your package name.'));
echo html_ac(html_ap() -1);

//Show a list of existing packages for this project so they can be edited
if (count($FRSPackages) == 0) {
	echo $HTML->information(_('There are no packages defined.'));
} else {
	$title_arr = array();
	$title_arr[] = _('Releases');
	$title_arr[] = _('Package name');
	$title_arr[] = _('Status');
	$title_arr[] = _('Publicly Viewable');

	echo $HTML->openForm(array('action' => util_make_uri('/frs/?group_id='.$group_id.'&action=updatepackage'), 'method' => 'post'));
	echo $HTML->listTableTop($title_arr);
	foreach ($FRSPackages as $key => $FRSPackage) {
		$cells = array();
		$content = util_make_link('/frs/?view=qrs&package_id='.$FRSPackage->getID().'&group_id='.$group_id, '<strong>['._('Add Release').']</strong>');
		if (count($FRSPackage->getReleases())) {
			$content .= util_make_link('/frs/?view=showreleases&package_id='.$FRSPackage->getID().'&group_id='.$group_id, '<strong>['._('Edit Releases').']</strong>');
		}
		$cells[] = array($content, 'style' => 'white-space: nowrap;', 'align' => 'center');
		$cells[][] = html_e('input', array('type' => 'hidden', 'name' => 'package_id', 'value' => $FRSPackage->getID())).html_e('input', array('type' => 'text', 'name' => 'package_name', 'value' => $FRSPackage->getName(), 'size' => 20, 'maxlength' => 60, 'required' => 'required', 'pattern' => '.{3,}', 'title' => _('At least 3 characters')));
		$cells[][] = frs_show_status_popup('status_id', $FRSPackage->getStatus());
		$cells[][] = frs_show_public_popup('is_public', $FRSPackage->isPublic());
		$deleteUrlAction = util_make_uri('/frs/?action=deletepackage&package_id='.$FRSPackage->getID().'&group_id='.$group_id);
		$cells[][] = html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Update'))).util_make_link('#', $HTML->getDeletePic(_('Delete this package'), _('Delete package')), array('onclick' => 'javascript:controllerFRS.toggleConfirmBox({idconfirmbox: \'confirmbox1\', do: \''._('Delete the package').' '.$FRSPackage->getName().'\', cancel: \''._('Cancel').'\', height: 150, width: 300, action: \''.$deleteUrlAction.'\'})' ), true);
		echo $HTML->multiTableRow(array('class' => $HTML->boxGetAltRowStyle($key, true), 'id' => 'pkgid'.$FRSPackage->getID()), $cells);
	}
	echo $HTML->listTableBottom();
	echo $HTML->closeForm();
}

echo $HTML->jQueryUIconfirmBox('confirmbox1', _('Delete package'), _('You are about to delete permanently this package. Are you sure? This action is definitive.'));

/*
	form to create a new package
*/

echo html_ao('fieldset');
echo html_e('legend', array(), _('Create New Package'));
echo $HTML->openForm(array('action' => util_make_uri('/frs/?group_id='.$group_id.'&action=addpackage'), 'method' => 'post'));
echo html_e('p', array(), html_e('strong', array(), _('New Package Name')._(':')).html_e('input', array('type' => 'text', 'name' => 'package_name', 'size' => 20, 'maxlength' => 30, 'required' => 'required', 'pattern' => '.{3,}', 'title' => _('At least 3 characters'))));
echo html_e('p', array(), html_e('strong', array(), _('Publicly Viewable')._(':')).
			html_e('input', array('type' => 'radio', 'name' => 'is_public', 'value' => 1, 'checked' => 'checked'))._('Public').
			html_e('input', array('type' => 'radio', 'name' => 'is_public', 'value' => 0))._('Private'));
echo html_e('p', array(), html_e('input', array('type' => 'submit', 'name' => 'submit', 'value' => _('Create This Package'))));
echo $HTML->closeForm();
echo html_ac(html_ap() - 1);
