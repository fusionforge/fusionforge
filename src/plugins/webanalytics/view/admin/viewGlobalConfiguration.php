<?php
/**
 * webanalyticsPlugin Global Configuration View
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

$linksArray = $webanalytics->getAvailableLinks();
if (sizeof($linksArray)) {
	echo $HTML->boxTop(_('Manage available links'));
	$tabletop = array(_('Name'), _('Standard JavaScript Tracking code'), _('Is Active'), _('Actions'));
	$classth = array('','','','unsortable');
	echo $HTML->listTableTop($tabletop, array(), 'sortable_webanalytics_listlinks', 'sortable', $classth);
	foreach ($linksArray as $link) {
		$cells = array();
		$cells[][] = htmlentities($link['name']);
		$cells[][] = html_e('code', array(), $link['url']);
		if ($link['is_enable']) {
			$cells[][] = html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'title'=>_('link is on')));
			$nextcell = util_make_link('/plugins/'.$webanalytics->name.'/?type=globaladmin&action=updateLinkStatus&linkid='.$link['id_webanalytics'].'&linkstatus=0', html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))), array('title' =>_('Desactivate this link')));
		} else {
			$cells[][] = html_image('docman/delete-directory.png', 22, 22, array('alt'=>_('link is off'), 'title'=>_('link is off')));
			$nextcell = util_make_link('/plugins/'.$webanalytics->name.'/?type=globaladmin&action=updateLinkStatus&linkid='.$link['id_webanalytics'].'&linkstatus=1', html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))), array('title' => _('Activate this link')));
		}
		$nextcell .= util_make_link('/plugins/'.$webanalytics->name.'/?type=globaladmin&view=updateLinkValue&linkid='.$link['id_webanalytics'], html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))), array('title' => _('Edit this link')));
		$nextcell .= util_make_link('/plugins/'.$webanalytics->name.'/?type=globaladmin&action=deleteLink&linkid='.$link['id_webanalytics'], $HTML->getDeletePic('', '', array('alt' => _('Delete this link'), 'title' => _('Delete this link'))));
		$cells[][] = $nextcell;
		echo $HTML->multiTableRow(array(), $cells);
	}
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	echo html_e('br');
}

echo $HTML->openForm(array('method' => 'post', 'name' => 'addLink', 'action' => '/plugins/'.$webanalytics->name.'/?type=globaladmin&action=addLink'));
echo $HTML->boxTop(_('Add a new webanalytics reference'));
echo $HTML->listTableTop();
$cells = array();
$cells[] = array(_('Informative Name').utils_requiredField()._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('name' => 'name', 'type' => 'text', 'maxsize' => 255, 'required' => 'required'));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Standard JavaScript Tracking code').utils_requiredField()._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('textarea', array('name' => 'link', 'rows' => 15, 'cols' => 70, 'required' => 'required', 'placeholder' => _('Just paste your code here...')), '', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(html_e('input', array('type' => 'submit', 'value' => _('Add'))), 'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo $HTML->boxBottom();
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
