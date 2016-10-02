<?php
/**
 * headermenu : viewGlobalConfiguration page
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
global $type;

$actionurl = '/plugins/'.$headermenu->name.'/?type='.$type;
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
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
	});
});

//]]>
<?php
echo html_ac(html_ap() - 1);
$linksHeaderMenuArray = $headermenu->getAvailableLinks('headermenu');
$linksOuterMenuArray = $headermenu->getAvailableLinks('outermenu');

if (sizeof($linksHeaderMenuArray) || sizeof($linksOuterMenuArray)) {
	echo $HTML->information(_('You can reorder tabs, just drag & drop rows in the table below and save order. Please note that those extra tabs can only appear after the standard tabs. And you can only move them inside the set of extra tabs.'));
}
if (sizeof($linksHeaderMenuArray)) {
	echo html_e('h2', array(), _('Manage available tabs in headermenu'), false);
	$tabletop = array(_('Order'), _('Tab Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_headermenu_listlinks', 'sortable', $classth);
	foreach ($linksHeaderMenuArray as $link) {
		$cells = array();
		$cells[] = array($link['ordering'], 'class' => 'align-center');
		if (strlen($link['url']) > 0) {
			$cells[][] = _('URL').' ('.htmlspecialchars($link['url']).')';
		} else {
			$cells[][] = _('HTML Page');
		}
		$cells[][] = htmlspecialchars($link['name']);
		$cells[][] = htmlspecialchars($link['description']);
		if ($link['is_enable']) {
			$cells[][] = html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'title'=>_('link is on')));
			$content = util_make_link($actionurl.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=0', html_image('docman/release-document.png', 22, 22, array('alt' => _('Desactivate this link'))), array('title' => _('Desactivate this link')));
		} else {
			$cells[][] = $HTML->getRemovePic('', '', array('alt'=>_('link is off'), 'title'=>_('link is off')));
			$content = util_make_link($actionurl.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=1', html_image('docman/reserve-document.png', 22, 22, array('alt' => _('Activate this link'))), array('title' => _('Activate this link')));
		}
		$content .= util_make_link($actionurl.'&view=updateLinkValue&linkid='.$link['id_headermenu'], $HTML->getEditFilePic(_('Edit this link'), 'editlink'), array('title' => _('Edit this link')));
		$content .= util_make_link($actionurl.'&action=deleteLink&linkid='.$link['id_headermenu'], $HTML->getDeletePic(_('Delete this link'), 'deletelink'), array('title' => _('Delete this link')));
		$cells[][] = $content;
		echo $HTML->multiTableRow(array('id' => $link['id_headermenu']), $cells);
	}
	echo $HTML->listTableBottom();
	echo html_e('input', array('type' => 'button', 'id' => 'linkorderheadervalidatebutton', 'value' => _('Save Order'), 'class' => 'hide'));
	echo html_e('br');
} else {
	echo $HTML->information(_('No tabs available for headermenu'));
}

if (sizeof($linksOuterMenuArray)) {
	echo html_e('h2', array(), _('Manage available tabs in outermenu'), false);
	$tabletop = array(_('Order'), _('Tab Type'), _('Displayed Name'), _('Description'), _('Status'), _('Actions'));
	$classth = array('', '', '', '', '', 'unsortable');
	echo $HTML->listTableTop($tabletop, false, 'sortable_outermenu_listlinks', 'sortable', $classth);
	foreach ($linksOuterMenuArray as $link) {
		$cells = array();
		$cells[] = array($link['ordering'], 'class' => 'align-center');
		if (strlen($link['url']) > 0) {
			$cells[][] = _('URL').' ('.htmlspecialchars($link['url']).')';
		} else {
			$cells[][] = _('HTML Page');
		}
		$cells[][] = htmlspecialchars($link['name']);
		$cells[][] = htmlspecialchars($link['description']);
		if ($link['is_enable']) {
			$cells[][] = html_image('docman/validate.png', 22, 22, array('alt'=>_('link is on'), 'title'=>_('link is on')));
			$content = util_make_link($actionurl.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=0', html_image('docman/release-document.png', 22, 22, array('alt'=>_('Desactivate this link'))), array('title' => _('Desactivate this link')));
		} else {
			$cells[][] = $HTML->getRemovePic('', '', array('alt'=>_('link is off'), 'title'=>_('link is off')));
			$content = util_make_link($actionurl.'&action=updateLinkStatus&linkid='.$link['id_headermenu'].'&linkstatus=1', html_image('docman/reserve-document.png', 22, 22, array('alt'=>_('Activate this link'))), array('title' => _('Activate this link')));
		}
		$content .= util_make_link($actionurl.'&view=updateLinkValue&linkid='.$link['id_headermenu'], $HTML->getEditFilePic(_('Edit this link'), 'editlink'), array('title' => _('Edit this link')));
		$content .= util_make_link($actionurl.'&action=deleteLink&linkid='.$link['id_headermenu'], $HTML->getDeletePic(_('Delete this link'), 'deletelink'), array('title' => _('Delete this link')));
		$cells[][] = $content;
		echo $HTML->multiTableRow(array('id' => $link['id_headermenu']), $cells);
	}
	echo $HTML->listTableBottom();
	echo html_e('input', array('type' => 'button', 'id' => 'linkorderoutervalidatebutton', 'value' => _('Save Order'), 'class' => 'hide'));
	echo html_e('br');
} else {
	echo $HTML->information(_('No tabs available for outermenu'));
}

echo html_e('h2', array(), _('Add new tab'), false);
echo $HTML->information(_('You can add specific tabs in outermenu (main tab) or headermenu (next to the login) with the form below.'));
echo $HTML->openForm(array('method' => 'POST', 'name' => 'addLink', 'action' => $actionurl.'&action=addLink'));
echo $HTML->listTableTop();
$cells = array();
$cells[] = array(_('Displayed Name').utils_requiredField()._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('required' => 'required', 'name' => 'name', 'type' => 'text', 'maxlength' => 255, 'size' => 40, 'placeholder' => _('the displayed name in menu')));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Description').utils_requiredField()._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('name' => 'description', 'type' => 'text', 'maxlength' => 255, 'size' => 40, 'placeholder' => _('the description, used by the tooltip system')));
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Menu Location').utils_requiredField()._(':'), 'style' => 'text-align:right');
$vals = array('headermenu', 'outermenu');
$texts = array('headermenu', 'outermenu');
$select_name = 'linkmenu';
$cells[][] = html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'headermenu', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('Tab Type').utils_requiredField()._(':'), 'style' => 'text-align:right');
$texts = array('URL', 'HTML Page');
$vals = array('url', 'htmlcode');
$select_name = 'typemenu';
$cells[][] = html_build_radio_buttons_from_arrays($vals, $texts, $select_name, 'url', false);
echo $HTML->multiTableRow(array(), $cells);
$cells = array();
$cells[] = array(_('HTML Page')._(':'), 'style' => 'text-align:right');
$params['name'] = 'htmlcode';
$params['body'] = _('Just paste your code here...');
$params['width'] = "800";
$params['height'] = "500";
$params['content'] = '<textarea name="htmlcode" rows="5" cols="80">'.$params['body'].'</textarea>';
plugin_hook_by_reference("text_editor", $params);
$cells[][] = $params['content'];
echo $HTML->multiTableRow(array('id' => 'trhtmlcode', 'class' => 'hide'), $cells);
$cells = array();
$cells[] = array(_('URL')._(':'), 'style' => 'text-align:right');
$cells[][] = html_e('input', array('name' => 'link', 'type' => 'url', 'maxlength' => 255, 'size' => 40));
echo $HTML->multiTableRow(array('id' => 'urlcode', 'class' => 'hide'), $cells);
$cells = array();
$cells[] = array(html_e('input', array('type' => 'submit', 'value' => _('Add'))), 'colspan' => 2);
echo $HTML->multiTableRow(array(), $cells);
echo $HTML->listTableBottom();
echo $HTML->closeForm();
echo $HTML->addRequiredFieldsInfoBox();
