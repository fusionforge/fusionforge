<?php
/**
 * webanalyticsPlugin Global Configuration View
 *
 * Copyright 2012, 2014, Franck Villaume - TrivialDev
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
	echo $HTML->listTableTop($tabletop, false, 'sortable_webanalytics_listlinks', 'sortable', $classth);
	foreach ($linksArray as $link) {
		echo '<tr>';
		echo '<td>'.htmlentities($link['name']).'</td>';
		echo '<td><code>'.$link['url'].'</code></td>';
		if ($link['is_enable']) {
			echo '<td>'.html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'title'=>_('link is on'))).'</td>';
			echo '<td><a title="'._('Desactivate this link').'" href="index.php?type=globaladmin&action=updateLinkStatus&linkid='.$link['id_webanalytics'].'&linkstatus=0">'.html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))). '</a>';
		} else {
			echo '<td>'.html_image('docman/delete-directory.png', 22, 22, array('alt'=>_('link is off'), 'title'=>_('link is off'))).'</td>';
			echo '<td><a title="'._('Activate this link').'" href="index.php?type=globaladmin&action=updateLinkStatus&linkid='.$link['id_webanalytics'].'&linkstatus=1">'.html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))). '</a>';
		}
		echo '<a title="'._('Edit this link').'" href="index.php?type=globaladmin&view=updateLinkValue&linkid='.$link['id_webanalytics'].'">'.html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))). '</a>';
		echo '<a title="'._('Delete this link').'" href="index.php?type=globaladmin&action=deleteLink&linkid='.$link['id_webanalytics'].'">'.html_image('docman/trash-empty.png',22,22, array('alt'=>_('Delete this link'))). '</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	echo '</br>';
}

echo $HTML->openForm(array('method' => 'POST', 'name' => 'addLink', 'action' => util_make_uri('/plugins/'.$webanalytics->name.'?type=globaladmin&action=addLink')));
echo $HTML->boxTop(_('Add a new webanalytics reference'));
echo $HTML->listTableTop();
$cells = array();
$cells[][] = _('Informative Name');
$cells[][] = '<input name="name" type="text" maxsize="255" required="required" />';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[][] = _('Standard JavaScript Tracking code');
$cells[][] = '<textarea name="link" rows="15" cols="70" required="required" >'._('Just paste your code here...').'</textarea>';
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array('<input type="submit" value="'. _('Add') .'" />', 'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo $HTML->boxBottom();
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
