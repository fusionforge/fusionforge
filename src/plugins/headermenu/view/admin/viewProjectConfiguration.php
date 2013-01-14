<?php
/**
 * headermenu : viewProjectConfiguration page
 *
 * Copyright 2012-2013, Franck Villaume - TrivialDev
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
global $group_id;

?>

<script language="Javascript" type="text/javascript">//<![CDATA[
var controllerGroupMenu;

jQuery(document).ready(function() {
	controllerGroupMenu = new GroupMenuController({
		inputHtmlCode:	jQuery('#typemenu_htmlcode'),
		inputURL:	jQuery('#typemenu_url'),
		trHtmlCode:	jQuery('#htmlcode'),
		trUrlCode:	jQuery('#urlcode'),
		trIframeView:	jQuery('#iframe'),
		tableTbodyLink:	jQuery('#sortable tbody'),
		validateButton:	jQuery('#linkordervalidatebutton'),
		groupId:	'<?php echo $group_id ?>',
		headerMenuUrl:	'<?php util_make_uri("/plugins/headermenu") ?>',
		validMessage:	'<?php echo _('Link Order successfully validated') ?>',
		errorMessage:	'<?php echo _('Error in Link Order validation') ?>',
	});
});

//]]></script>

<?php
$linksArray = $headermenu->getAvailableLinks('groupmenu');
if (sizeof($linksArray)) {
	echo '<p class="information">'. _('You can reorder links, just drag & drop rows in the table below and save order. Please note that those extra tabs can only appear after the standard tabs. And you can only move them inside the set of extra tabs.').'</p>';
	echo $HTML->boxTop(_('Manage available links'));
	$tabletop = array(_('Order'), _('Menu Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_headermenu_listlinks', 'sortable', $classth);
	foreach ($linksArray as $link) {
		echo '<tr id="'.$link['id_headermenu'].'" ><td>'.$link['ordering'].'</td>';
		if (strlen($link['url']) > 0) {
			echo '<td>'._('URL');
			if ($link['linktype'] == 'iframe') {
				echo ' '._('displayed as iframe');
			}
			echo ' ('.htmlspecialchars($link['url']).')</td>';
		} else {
			echo '<td>'._('static html code').'</td>';
		}
		echo '<td>'.htmlspecialchars($link['name']).'</td>';
		echo '<td>'.htmlspecialchars($link['description']).'</td>';
		if ($link['is_enable']) {
			echo '<td>'.html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'class'=>'tabtitle', 'title'=>_('link is on'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Desactivate this link').'" href="index.php?type=projectadmin&group_id='.$group_id.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=0">'.html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))). '</a>';
		} else {
			echo '<td>'.html_image('docman/delete-directory.png', 22, 22, array('alt'=>_('link is off'), 'class'=>'tabtitle', 'title'=>_('link is off'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Activate this link').'" href="index.php?type=projectadmin&group_id='.$group_id.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=1">'.html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))). '</a>';
		}
		echo '<a class="tabtitle-ne" title="'._('Edit this link').'" href="index.php?type=projectadmin&group_id='.$group_id.'&view=updateLinkValue&linkid='.$link['id_headermenu'].'">'.html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))). '</a>';
		echo '<a class="tabtitle-ne" title="'._('Delete this link').'" href="index.php?type=projectadmin&group_id='.$group_id.'&action=deleteLink&linkid='.$link['id_headermenu'].'">'.html_image('docman/trash-empty.png',22,22, array('alt'=>_('Delete this link'))). '</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo $HTML->listTableBottom();
	echo $HTML->boxBottom();
	echo '<input type="button" id="linkordervalidatebutton" value="'._('Save Order').'" style="display:none;" />';
	echo '</br>';
}

echo '<p class="information">'._('You can add your own tabs in the menu bar with the form below.').'</p>';
echo '<form method="POST" name="addLink" action="index.php?type=projectadmin&group_id='.$group_id.'&action=addLink">';
echo '<table><tr>';
echo $HTML->boxTop(_('Add a new link'));
echo '<td>'._('Displayed Name').'</td><td><input name="name" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Description').'</td><td><input name="description" type="text" maxsize="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Menu Type').'</td><td>';
$texts = array('URL', 'New Page');
$vals = array('url', 'htmlcode');
$select_name = 'typemenu';
echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'url', false);
echo '</td>';
echo '</tr><tr id="htmlcode" style="display:none">';
echo '<td>'._('Your HTML Code.').'</td><td>';
$GLOBALS['editor_was_set_up'] = false;
$body = _('Just paste your code here...');
$params['name'] = 'htmlcode';
$params['body'] = $body;
$params['width'] = "800";
$params['height'] = "500";
$params['user_id'] = user_getid();
plugin_hook("text_editor", $params);
if (!$GLOBALS['editor_was_set_up']) {
	echo '<textarea name="htmlcode" rows="5" cols="80">'.$body.'</textarea>';
}
unset($GLOBALS['editor_was_set_up']);
echo '</td></tr><tr id="urlcode" >';
echo '<td>'._('URL').'</td><td><input name="link" type="text" maxsize="255" /></td>';
echo '</td></tr><tr id="iframe" >';
echo '<td colspan="2" ><input name="iframeview" type="checkbox" value="1" />Display URL as iframe.</td>';
echo '</tr><tr>';
echo '<td>';
echo '<input type="hidden" name="linkmenu" value="groupmenu" />';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo $HTML->boxBottom();
echo '</tr></table>';
echo '</form>';
