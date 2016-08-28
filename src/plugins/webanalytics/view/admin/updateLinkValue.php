<?php
/**
 * webanalyticsPlugin Class
 *
 * Copyright 2012,2014,2016, Franck Villaume - TrivialDev
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

global $HTML;
global $webanalytics;

session_require_global_perm('forge_admin');
$linkId = getIntFromRequest('linkid');

$linkValues = $webanalytics->getLink($linkId);
if (is_array($linkValues)) {
	echo $HTML->openForm(array('method' => 'POST', 'name' => 'updateLink', 'action' => '/plugins/'.$webanalytics->name.'/?type=globaladmin&action=updateLinkValue'));
	echo $HTML->boxTop(_('Update this link'));
	echo $HTML->listTableTop();
	$cells = array();
	$cells[][] = _('Informative Name').utils_requiredField()._(':');
	$cells[][] = '<input name="name" type="text" maxsize="255" value="'.$linkValues['name'].'" required="required" />';
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[][] = _('Standard JavaScript Tracking code').utils_requiredField()._(':');
	$cells[][] = '<textarea name="link" rows="15" cols="70" required="required" >'.$linkValues['url'].'</textarea>';
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array('<input type="hidden" name="linkid" value="'.$linkId.'" />'.
			'<input type="submit" value="'. _('Update') .'" />'.
			util_make_link('/plugins/'.$webanalytics->name.'?type=globaladmin', '<input type="button" value="'. _('Cancel') .'" />'), 'colspan' => 2);
	echo $HTML->multiTableRow(array(), $cells);
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	echo $HTML->closeForm();
	echo $HTML->addRequiredFieldsInfoBox();
} else {
	$error_msg = _('Cannot retrieve value for this link')._(': ').$linkId;
	session_redirect('plugins/'.$webanalytics->name.'/?type=globaladmin');
}
