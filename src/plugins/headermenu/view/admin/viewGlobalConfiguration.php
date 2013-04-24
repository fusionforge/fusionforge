<?php
/**
 * headermenu : viewGlobalConfiguration page
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

?>

<script type="text/javascript">//<![CDATA[
var controllerHeaderMenu;

jQuery(document).ready(function() {
	controllerHeaderMenu = new HeaderMenuController({
		inputHtmlCode:	jQuery('#typemenu_htmlcode'),
		inputURL:	jQuery('#typemenu_url'),
		inputHeader:	jQuery('#linkmenu_headermenu'),
		inputOuter:	jQuery('#linkmenu_outermenu'),
		trHtmlCode:	jQuery('#trhtmlcode'),
		trUrlCode:	jQuery('#urlcode'),
		tableOutTbLink:	jQuery('.sortable_outermenu_listlinks tbody'),
		tableHeaTbLink: jQuery('.sortable_headermenu_listlinks tbody'),
		validOutButton:	jQuery('#linkorderoutervalidatebutton'),
		validHeaButton:	jQuery('#linkorderheadervalidatebutton'),
		validMessOut:	'<?php echo _('Outermenu Link Order successfully validated') ?>',
		validMessHea:	'<?php echo _('Headermenu Link Order successfully validated') ?>',
		errMessOut:	'<?php echo _('Error in Outermenu Link Order validation') ?>',
		errMessHea:	'<?php echo _('Error in Headermenu Link Order validation') ?>'
	});
});

//]]></script>

<?php
$linksHeaderMenuArray = $headermenu->getAvailableLinks('headermenu');
$linksOuterMenuArray = $headermenu->getAvailableLinks('outermenu');

if (sizeof($linksHeaderMenuArray) || sizeof($linksOuterMenuArray)) {
	echo '<p class="information">'. _('You can reorder tabs, just drag & drop rows in the table below and save order. Please note that those extra tabs can only appear after the standard tabs. And you can only move them inside the set of extra tabs.').'</p>';
}
if (sizeof($linksHeaderMenuArray)) {
	echo '<h2>'._('Manage available tabs in headermenu').'</h2>';
	$tabletop = array(_('Order'), _('Tab Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_headermenu_listlinks', 'sortable', $classth);
	foreach ($linksHeaderMenuArray as $link) {
		echo '<tr id="'.$link['id_headermenu'].'" ><td>'.$link['ordering'].'</td>';
		if (strlen($link['url']) > 0) {
			echo '<td>'._('URL').' ('.htmlspecialchars($link['url']).')</td>';
		} else {
			echo '<td>'._('HTML Page').'</td>';
		}
		echo '<td>'.htmlspecialchars($link['name']).'</td>';
		echo '<td>'.htmlspecialchars($link['description']).'</td>';
		if ($link['is_enable']) {
			echo '<td>'.html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'class'=>'tabtitle', 'title'=>_('link is on'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Desactivate this link').'" href="index.php?type=globaladmin&amp;action=updateLinkStatus&amp;linkid='.$link['id_headermenu'].'&amp;linkstatus=0">'.html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))). '</a>';
		} else {
			echo '<td>'.html_image('docman/delete-directory.png', 22, 22, array('alt'=>_('link is off'), 'class'=>'tabtitle', 'title'=>_('link is off'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Activate this link').'" href="index.php?type=globaladmin&amp;action=updateLinkStatus&amp;linkid='.$link['id_headermenu'].'&amp;linkstatus=1">'.html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))). '</a>';
		}
		echo '<a class="tabtitle-ne" title="'._('Edit this link').'" href="index.php?type=globaladmin&amp;view=updateLinkValue&amp;linkid='.$link['id_headermenu'].'">'.html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))). '</a>';
		echo '<a class="tabtitle-ne" title="'._('Delete this link').'" href="index.php?type=globaladmin&amp;action=deleteLink&amp;linkid='.$link['id_headermenu'].'">'.html_image('docman/trash-empty.png',22,22, array('alt'=>_('Delete this link'))). '</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo $HTML->listTableBottom();
	echo '<input type="button" id="linkorderheadervalidatebutton" value="'._('Save Order').'" style="display:none;" />';
	echo '<br/>';
} else {
	echo '<p class="information">'._('No tabs available for headermenu').'</p>';
}

if (sizeof($linksOuterMenuArray)) {
	echo '<h2>'._('Manage available tabs in outermenu').'</h2>';
	$tabletop = array(_('Order'), _('Tab Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_outermenu_listlinks', 'sortable', $classth);
	foreach ($linksOuterMenuArray as $link) {
		echo '<tr id="'.$link['id_headermenu'].'" ><td>'.$link['ordering'].'</td>';
		if (strlen($link['url']) > 0) {
			echo '<td>'._('URL').' ('.htmlspecialchars($link['url']).')</td>';
		} else {
			echo '<td>'._('HTML Page').'</td>';
		}
		echo '<td>'.htmlspecialchars($link['name']).'</td>';
		echo '<td>'.htmlspecialchars($link['description']).'</td>';
		if ($link['is_enable']) {
			echo '<td>'.html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'class'=>'tabtitle', 'title'=>_('link is on'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Desactivate this link').'" href="index.php?type=globaladmin&amp;action=updateLinkStatus&amp;linkid='.$link['id_headermenu'].'&amp;linkstatus=0">'.html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))). '</a>';
		} else {
			echo '<td>'.html_image('docman/delete-directory.png', 22, 22, array('alt'=>_('link is off'), 'class'=>'tabtitle', 'title'=>_('link is off'))).'</td>';
			echo '<td><a class="tabtitle-ne" title="'._('Activate this link').'" href="index.php?type=globaladmin&amp;action=updateLinkStatus&amp;linkid='.$link['id_headermenu'].'&amp;linkstatus=1">'.html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))). '</a>';
		}
		echo '<a class="tabtitle-ne" title="'._('Edit this link').'" href="index.php?type=globaladmin&amp;view=updateLinkValue&amp;linkid='.$link['id_headermenu'].'">'.html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))). '</a>';
		echo '<a class="tabtitle-ne" title="'._('Delete this link').'" href="index.php?type=globaladmin&amp;action=deleteLink&amp;linkid='.$link['id_headermenu'].'">'.html_image('docman/trash-empty.png',22,22, array('alt'=>_('Delete this link'))). '</a>';
		echo '</td>';
		echo '</tr>';
	}
	echo $HTML->listTableBottom();
	echo '<input type="button" id="linkorderoutervalidatebutton" value="'._('Save Order').'" style="display:none;" />';
	echo '<br/>';
} else {
	echo '<p class="information">'._('No tabs available for outermenu').'</p>';
}

echo '<h2>'._('Add new tab').'</h2>';
echo '<p class="information">'._('You can add specific tabs in outermenu (main tab) or headermenu (next to the login) with the form below.').'</p>';
echo '<form method="POST" name="addLink" action="index.php?type=globaladmin&amp;action=addLink">';
echo '<table class="infotable"><tr>';
echo '<td>'._('Displayed Name')._(':').'</td><td><input required="required" name="name" type="text" maxlength="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Description')._(':').'</td><td><input name="description" type="text" maxlength="255" /></td>';
echo '</tr><tr>';
echo '<td>'._('Menu Location')._(':').'</td><td>';
$vals = array('headermenu', 'outermenu');
$texts = array('headermenu', 'outermenu');
$select_name = 'linkmenu';
echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'headermenu', false);
echo '</td>';
echo '</tr><tr>';
echo '<td>'._('Tab Type')._(':').'</td><td>';
$texts = array('URL', 'HTML Page');
$vals = array('url', 'htmlcode');
$select_name = 'typemenu';
echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'url', false);
echo '</td>';
echo '</tr><tr id="trhtmlcode" style="display:none">';
echo '<td>'._('HTML Page')._(':').'</td><td>';
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
echo '<td>'._('URL')._(':').'</td><td><input name="link" type="text" maxlength="255" /></td>';
echo '</tr><tr>';
echo '<td colspan="2">';
echo '<input type="submit" value="'. _('Add') .'" />';
echo '</td>';
echo '</tr></table>';
echo '</form>';
