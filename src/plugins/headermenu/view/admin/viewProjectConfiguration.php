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

<script type="text/javascript">//<![CDATA[
var controllerGroupMenu;

jQuery(document).ready(function() {
	controllerGroupMenu = new GroupMenuController({
		inputHtmlCode:	jQuery('#typemenu_htmlcode'),
		inputURL:	jQuery('#typemenu_url'),
		trHtmlCode:	jQuery('#trhtmlcode'),
		trUrlCode:	jQuery('#urlcode'),
		trIframeView:	jQuery('#iframe'),
		tableTbodyLink:	jQuery('#sortable').find('tbody'),
		validateButton:	jQuery('#linkordervalidatebutton'),
		groupId:	'<?php echo $group_id ?>',
		headerMenuUrl:	'<?php util_make_uri("/plugins/headermenu") ?>',
		validMessage:	'<?php echo _('Link Order successfully validated') ?>',
		errorMessage:	'<?php echo _('Error in Link Order validation') ?>'
	});
});

//]]></script>

<?php
$linksArray = $headermenu->getAvailableLinks('groupmenu', $group_id);
if (sizeof($linksArray)) {
	echo '<h2>'._('Manage available tabs')."</h2>\n";
	echo '<p class="information">'. _('You can reorder tabs, just drag & drop rows in the table below and save order. Please note that those extra tabs can only appear after the standard tabs. And you can only move them inside the set of extra tabs.').'</p>';
	$tabletop = array(_('Order'), _('Tab Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_headermenu_listlinks', 'sortable', $classth);
	foreach ($linksArray as $link) {
		echo '<tr id="'.$link['id_headermenu'].'" ><td>'.$link['ordering']."</td>\n";
		if (strlen($link['url']) > 0) {
			echo '<td>'._('URL');
			if ($link['linktype'] == 'iframe') {
				echo ' '._('displayed as iframe');
			}
			echo ' ('.htmlspecialchars($link['url']).")</td>\n";
		} else {
			echo '<td>'._('HTML Page')."</td>\n";
		}
		echo '<td>'.htmlspecialchars($link['name'])."</td>\n";
		echo '<td>'.htmlspecialchars($link['description'])."</td>\n";
		if ($link['is_enable']) {
			echo '<td>'.html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'class'=>'tabtitle', 'title'=>_('link is on')))."</td>\n";
			echo '<td><a class="tabtitle-ne" title="'._('Desactivate this link').'" href="index.php?type=projectadmin&amp;group_id='.$group_id.'&amp;action=updateLinkStatus&amp;linkid='.$link['id_headermenu'].'&amp;linkstatus=0">'.html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))). '</a>';
		} else {
			echo '<td>'.html_image('docman/delete-directory.png', 22, 22, array('alt'=>_('link is off'), 'class'=>'tabtitle', 'title'=>_('link is off')))."</td>\n";
			echo '<td><a class="tabtitle-ne" title="'._('Activate this link').'" href="index.php?type=projectadmin&amp;group_id='.$group_id.'&amp;action=updateLinkStatus&amp;linkid='.$link['id_headermenu'].'&amp;linkstatus=1">'.html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))). '</a>';
		}
		echo '<a class="tabtitle-ne" title="'._('Edit this link').'" href="index.php?type=projectadmin&amp;group_id='.$group_id.'&amp;view=updateLinkValue&amp;linkid='.$link['id_headermenu'].'">'.html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))). '</a>';
		echo '<a class="tabtitle-ne" title="'._('Delete this link').'" href="index.php?type=projectadmin&amp;group_id='.$group_id.'&amp;action=deleteLink&amp;linkid='.$link['id_headermenu'].'">'.html_image('docman/trash-empty.png',22,22, array('alt'=>_('Delete this link'))). '</a>';
		echo "</td>\n";
		echo "</tr>\n";
	}
	echo $HTML->listTableBottom();
	echo '<input type="button" id="linkordervalidatebutton" value="'._('Save Order').'" style="display:none;" />';
	echo '<br/>';
}

echo '<h2>'._('Add new tab')."</h2>\n";
echo '<p class="information">'._('You can add your own tabs in the menu bar with the form below.').'</p>';
echo '<form method="POST" name="addLink" action="index.php?type=projectadmin&amp;group_id='.$group_id.'&amp;action=addLink">';
echo '<table class="infotable">'."\n";
echo '<tr>';
echo '<td>'._('Displayed Name').utils_requiredField()._(':')."</td>\n";
echo '<td><input required="required" name="name" type="text" maxlength="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Description')._(':').'</td><td><input name="description" type="text" maxlength="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Tab Type')._(':').'</td><td>';
$texts = array('URL', 'HTML Page');
$vals = array('url', 'htmlcode');
$select_name = 'typemenu';
echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'url', false);
echo '</td>';
echo '</tr>'."\n".'<tr id="trhtmlcode" style="display:none">';
echo '<td>'._('HTML Page').utils_requiredField()._(':').'</td><td>';

$params['name'] = 'htmlcode';
$params['body'] = _('Just paste your code here...');
$params['width'] = "800";
$params['height'] = "500";
$params['content'] = '<textarea name="htmlcode" rows="5" cols="80">'.$params['body'].'</textarea>';
plugin_hook_by_reference("text_editor", $params);
echo $params['content'];

echo '</td></tr><tr id="urlcode" >';
echo '<td>'._('URL').utils_requiredField()._(':').'</td><td><input name="link" type="text" maxlength="255" /></td>';
echo '</tr><tr id="iframe" >';
echo '<td colspan="2"><input name="iframeview" type="checkbox" value="1" />Display URL as iframe</td>';
echo '</tr><tr>';
echo '<td colspan="2">';
echo '<input type="hidden" name="linkmenu" value="groupmenu" />';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td></tr></table>';
echo '</form>';

echo utils_requiredField().' '._('Indicates required fields.');
