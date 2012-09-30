<?php
/**
 * headermenuPlugin Class
 *
 * Copyright 2012 Franck Villaume - TrivialDev
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
global $headermenu;

session_require_global_perm('forge_admin');
?>

<script language="Javascript" type="text/javascript">//<![CDATA[
var controllerHeaderMenu;

jQuery(document).ready(function() {
	controllerHeaderMenu = new HeaderMenuController({
		inputHtmlCode:	jQuery('#typemenu_htmlcode'),
		inputURL:	jQuery('#typemenu_url'),
		inputHeader:	jQuery('#linkmenu_headermenu'),
		inputOuter:	jQuery('#linkmenu_outermenu'),
		trHtmlCode:	jQuery('#htmlcode'),
		trUrlCode:	jQuery('#urlcode')
    });
});

//]]></script>

<?
$linksArray = $headermenu->getAllAvailableLinks();
if (sizeof($linksArray)) {
	echo $HTML->boxTop(_('Manage available links'));
	$tabletop = array(_('Menu Location'), _('Menu Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '','','','','unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_headermenu_listlinks', 'sortable', $classth);
	foreach ($linksArray as $link) {
		echo '<tr>';
		echo '<td>'.htmlspecialchars($link['linkmenu']).'</td>';
		if (strlen($link['url']) > 0) {
			echo '<td>'._('URL').' ('.htmlspecialchars($link['url']).')</td>';
		} else {
			echo '<td>'._('static html code').'</td>';
		}
		echo '<td>'.htmlspecialchars($link['name']).'</td>';
		echo '<td>'.htmlspecialchars($link['description']).'</td>';
		if ($link['is_enable']) {
			echo '<td>'.html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'class'=>'tabtitle', 'title'=>_('link is on'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Desactivate this link').'" href="index.php?type=globaladmin&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=0">'.html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))). '</a>';
		} else {
			echo '<td>'.html_image('docman/delete-directory.png', 22, 22, array('alt'=>_('link is off'), 'class'=>'tabtitle', 'title'=>_('link is off'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Activate this link').'" href="index.php?type=globaladmin&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=1">'.html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))). '</a>';
		}
		echo '<a class="tabtitle-ne" title="'._('Edit this link').'" href="index.php?type=globaladmin&view=updateLinkValue&linkid='.$link['id_headermenu'].'">'.html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))). '</a>';
		echo '<a class="tabtitle-ne" title="'._('Delete this link').'" href="index.php?type=globaladmin&action=deleteLink&linkid='.$link['id_headermenu'].'">'.html_image('docman/trash-empty.png',22,22, array('alt'=>_('Delete this link'))). '</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	echo '</br>';
}

echo '<form method="POST" name="addLink" action="index.php?type=globaladmin&action=addLink">';
echo '<table><tr>';
echo $HTML->boxTop(_('Add a new link'));
echo '<td>'._('Displayed Name').'</td><td><input name="name" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Description').'</td><td><input name="description" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Menu Location').'</td><td>';
$vals = array('headermenu', 'outermenu');
$texts = array('headermenu', 'outermenu');
$select_name = 'linkmenu';
echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'headermenu', false);
echo '</td>';
echo '</tr><tr>';
echo '<td>'._('Menu Type').'</td><td>';
$texts = array('URL', 'New Page');
$vals = array('url', 'htmlcode');
$select_name = 'typemenu';
echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'url', false);
echo '</td>';
echo '</tr><tr id="htmlcode" style="display:none">';
echo '<td>'._('Your HTML Code.').'</td><td><textarea name="htmlcode" rows="15" cols="70">'._('Just paste your code here...').'</textarea></td>';
echo '</tr><tr id="urlcode" >';
echo '<td>'._('URL').'</td><td><input name="link" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo $HTML->boxBottom();
echo '</tr></table>';
echo '</form>';
