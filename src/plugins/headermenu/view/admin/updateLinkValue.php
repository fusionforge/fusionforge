<?php
/**
 * headermenu plugin : updateLinkValue view
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
global $group_id;

$linkId = getIntFromRequest('linkid');
$redirect_url = '/plugins/'.$headermenu->name.'/?type='.$type;
$action_url = $redirect_url.'&action=updateLinkValue';
if (isset($group_id) && $group_id) {
	$redirect_url .= '&group_id='.$group_id;
	$action_url .= '&group_id='.$group_id;
}
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
var controllerHeaderMenu;

jQuery(document).ready(function() {
	controllerHeaderMenu = new EditHeaderMenuController({
		inputHtmlCode:	jQuery('#typemenu_htmlcode'),
		inputURL:	jQuery('#typemenu_url'),
		inputURLIframe:	jQuery('#typemenu_iframe'),
		inputHeader:	jQuery('#linkmenu_headermenu'),
		inputOuter:	jQuery('#linkmenu_outermenu'),
		trHtmlCode:	jQuery('#trhtmlcode'),
		trUrlCode:	jQuery('#urlcode')
    });
});

//]]>
<?php
echo html_ac(html_ap() - 1);
$linkValues = $headermenu->getLink($linkId);
if (is_array($linkValues)) {
	echo html_e('h2', array(), _('Update this link'));
	echo $HTML->openForm(array('method' => 'POST', 'name' => 'updateLink', 'action' => $action_url));
	echo $HTML->listTableTop();
	$cells = array();
	$cells[] = array(_('Displayed Name').utils_requiredField()._(':'), 'style' => 'text-align:right');
	$cells[][] = '<input required="required" name="name" type="text" maxlength="255" value="'.$linkValues['name'].'" />';
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array(_('Description')._(':'), 'style' => 'text-align:right');
	$cells[][] = '<input name="description" type="text" maxlength="255" value="'.$linkValues['description'].'" />';
	echo $HTML->multiTableRow(array(), $cells);
	if ($type == 'globaladmin') {
		$cells = array();
		$cells[] = array(_('Menu Location')._(':'), 'style' => 'text-align:right');
		$vals = array('headermenu', 'outermenu');
		$texts = array('headermenu', 'outermenu');
		$select_name = 'linkmenu';
		$cells[][] = html_build_radio_buttons_from_arrays($vals, $texts, $select_name, $linkValues['linkmenu'], false);
		echo $HTML->multiTableRow(array(), $cells);
		$texts = array('URL', 'HTML Page');
		$vals = array('url', 'htmlcode');
		$hidden = '';
	}
	$cells = array();
	$cells[] = array(_('Tab Type')._(':'), 'style' => 'text-align:right');
	if ($type == 'projectadmin') {
		$texts = array('URL', 'URL as iframe', 'HTML Page');
		$vals = array('url', 'iframe', 'htmlcode');
		$hidden = '<input type="hidden" name="linkmenu" value="groupmenu" />';
	}
	$select_name = 'typemenu';
	$cells[][] = $hidden.html_build_radio_buttons_from_arrays($vals, $texts, $select_name, $linkValues['linktype'], false);
	echo $HTML->multiTableRow(array(), $cells);
	$cells = array();
	$cells[] = array(_('HTML Page').utils_requiredField()._(':'), 'style' => 'text-align:right');
	$params['name'] = 'htmlcode';
	$params['body'] = $linkValues['htmlcode'];
	$params['width'] = "800";
	$params['height'] = "500";
	$params['content'] = '<textarea name="htmlcode" rows="5" cols="80">'.$params['body'].'</textarea>';
	plugin_hook_by_reference("text_editor", $params);
	$cells[][] = $params['content'];
	echo $HTML->multiTableRow(array('id' => 'trhtmlcode', 'class' => 'hide'), $cells);
	$cells = array();
	$cells[] = array(_('URL').utils_requiredField()._(':'), 'style' => 'text-align:right');
	$cells[][] = '<input name="link" type="text" maxlength="255" value="'.$linkValues['url'].'" />';
	echo $HTML->multiTableRow(array('id' => 'urlcode', 'class' => 'hide'), $cells);
	echo $HTML->listTableBottom();
	echo html_e('p', array(), '<input type="hidden" name="linkid" value="'.$linkId.'" />'.
				'<input type="submit" value="'. _('Update') .'" />'.
				'<input type="submit" name="submit" value="'. _('Cancel') .'" formnovalidate="formnovalidate" />', false);
	echo $HTML->closeForm();
	echo $HTML->addRequiredFieldsInfoBox();
} else {
	$error_msg = _('Cannot retrieve value for this link')._(': ').$linkId;
	session_redirect($redirect_url);
}
