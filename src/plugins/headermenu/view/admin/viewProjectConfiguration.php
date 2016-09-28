<?php
/**
 * headermenu : viewProjectConfiguration page
 *
 * Copyright 2012-2014,2016, Franck Villaume - TrivialDev
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

echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
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
		headerMenuUrl:	'<?php echo util_make_uri('/plugins/'.$headermenu->name) ?>'
	});
});

//]]>
<?php
echo html_ac(html_ap() - 1);
$linksArray = $headermenu->getAvailableLinks('groupmenu', $group_id);
if (sizeof($linksArray)) {
	echo html_e('h2', array(), _('Manage available tabs'));
	echo $HTML->information(_('You can reorder tabs, just drag & drop rows in the table below and save order. Please note that those extra tabs can only appear after the standard tabs. And you can only move them inside the set of extra tabs.'));
	$tabletop = array(_('Order'), _('Tab Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_headermenu_listlinks', 'sortable', $classth);
	foreach ($linksArray as $link) {
		$cells = array();
		$cells[] = array($link['ordering'], 'class' => 'align-center');
		if (strlen($link['url']) > 0) {
			$content = _('URL');
			if ($link['linktype'] == 'iframe') {
				$content .= ' '._('displayed as iframe');
			}
			$content .= ' ('.htmlspecialchars($link['url']).')';
		} else {
			$content = _('HTML Page');
		}
		$cells[][] = $content;
		$cells[][] = htmlspecialchars($link['name']);
		$cells[][] = htmlspecialchars($link['description']);
		if ($link['is_enable']) {
			$cells[][] = html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'title'=>_('link is on')));
			$actionsLinks = util_make_link('/plugins/'.$headermenu->name.'?type=projectadmin&group_id='.$group_id.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=0', html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))), array('title' => _('Desactivate this link')));
		} else {
			$cells[][] = $HTML->getRemovePic('', '', array('alt'=>_('link is off'), 'title'=>_('link is off')));
			$actionsLinks = util_make_link('/plugins/'.$headermenu->name.'?type=projectadmin&group_id='.$group_id.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=1', html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))), array('title' => _('Activate this link')));
		}
		$actionsLinks .= util_make_link('/plugins/'.$headermenu->name.'?type=projectadmin&group_id='.$group_id.'&view=updateLinkValue&linkid='.$link['id_headermenu'], html_image('docman/edit-file.png',22,22, array('alt'=>_('Edit this link'))), array('title' => _('Edit this link')));
		$actionsLinks .= util_make_link('/plugins/'.$headermenu->name.'?type=projectadmin&group_id='.$group_id.'&action=deleteLink&linkid='.$link['id_headermenu'], html_image('docman/trash-empty.png',22,22, array('alt'=>_('Delete this link'))), array('title' => _('Delete this link')));
		$cells[][] = $actionsLinks;
		echo $HTML->multiTableRow(array('id' => $link['id_headermenu']), $cells);
	}
	echo $HTML->listTableBottom();
	echo html_e('input', array('type' => 'button', 'id' => 'linkordervalidatebutton', 'value' => _('Save Order'), 'class' => 'hide'));
	echo html_e('br');
}

echo html_e('h2', array(), _('Add new tab'));
echo $HTML->information(_('You can add your own tabs in the menu bar with the form below.'));
echo $HTML->openForm(array('method' => 'POST', 'name' => 'addLink', 'action' => '/plugins/'.$headermenu->name.'/?type=projectadmin&group_id='.$group_id.'&action=addLink'));
echo $HTML->listTableTop();
$cells = array();
$cells[] = array(_('Displayed Name').utils_requiredField()._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('required' => 'required', 'name' => 'name', 'type' => 'text', 'maxlength' => 255));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Description')._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('name' => 'description', 'type' => 'text', 'maxlength' => 255));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Tab Type')._(':'), 'style' => 'text-align:right');
$texts = array('URL', 'HTML Page');
$vals = array('url', 'htmlcode');
$select_name = 'typemenu';
$cells[][] = html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'url', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('HTML Page').utils_requiredField()._(':'), 'style' => 'text-align:right');
$params['name'] = 'htmlcode';
$params['body'] = _('Just paste your code here...');
$params['width'] = "800";
$params['height'] = "500";
$params['content'] = '<textarea name="htmlcode" rows="5" cols="80">'.$params['body'].'</textarea>';
plugin_hook_by_reference("text_editor", $params);
$cells[][] = $params['content'];
echo $HTML->multiTableRow(array('id' => 'trhtmlcode', 'class' => 'hide'), $cells);
$cells = array();
$cells[] = array(_('URL').utils_requiredField()._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('name' => 'link', 'type' => 'url', 'maxlength' => 255));
echo $HTML->multiTableRow(array('id' => 'urlcode'), $cells);
$cells = array();
$cells[] = array(_('Display URL as iframe')._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('name' => 'iframeview', 'type' => 'checkbox', 'value' => 1));
echo $HTML->multiTableRow(array('id' => 'iframe'), $cells);
$cells = array();
$cells[] = array(html_e('input', array('type' => 'hidden', 'name' => 'linkmenu', 'value' => 'groupmenu')).html_e('input', array('type' => 'submit', 'value' => _('Add'))), 'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
