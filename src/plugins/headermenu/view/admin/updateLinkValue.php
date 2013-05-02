<?php
/**
 * headermenu plugin : updateLinkValue view
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
global $type;
global $group_id;

$linkId = getIntFromRequest('linkid');
$redirect_url = '/plugins/'.$headermenu->name.'/?type='.$type;
$action_url = 'index.php?type='.$type.'&amp;action=updateLinkValue';
if (isset($group_id) && $group_id) {
	$redirect_url .= '&amp;group_id='.$group_id;
	$action_url .= '&amp;group_id='.$group_id;
}
?>

<script type="text/javascript">//<![CDATA[
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

//]]></script>

<?php
$linkValues = $headermenu->getLink($linkId);
if (is_array($linkValues)) {
	echo '<h2>'._('Update this link')."</h2>\n";
	echo '<form method="POST" name="updateLink" action="'.$action_url.'">';
	echo '<table class="infotable">'."\n".'<tr>';
	echo '<td>'._('Displayed Name').utils_requiredField()._(':').'</td><td><input required="required" name="name" type="text" maxlength="255" value="'.$linkValues['name'].'" /></td>';
	echo '</tr>'."\n".'<tr>';
	echo '<td>'._('Description')._(':').'</td><td><input name="description" type="text" maxlength="255" value="'.$linkValues['description'].'" /></td>';
	echo '</tr>'."\n".'<tr>';
	if ($type == 'globaladmin') {
		echo '<td>'._('Menu Location')._(':').'</td><td>';
		$vals = array('headermenu', 'outermenu');
		$texts = array('headermenu', 'outermenu');
		$select_name = 'linkmenu';
		echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, $linkValues['linkmenu'], false);
		echo '</td>';
		echo '</tr><tr>';
		$texts = array('URL', 'HTML Page');
		$vals = array('url', 'htmlcode');
	}
	echo '<td>'._('Tab Type')._(':').'</td><td>';
	if ($type == 'projectadmin') {
		$texts = array('URL', 'URL as iframe', 'HTML Page');
		$vals = array('url', 'iframe', 'htmlcode');
		echo '<input type="hidden" name="linkmenu" value="groupmenu" />';
	}
	$select_name = 'typemenu';
	echo html_build_radio_buttons_from_arrays($vals, $texts, $select_name, $linkValues['linktype'], false);
	echo '</td>';
	echo '</tr>'."\n".'<tr id="trhtmlcode" style="display:none">';
	echo '<td>'._('HTML Page').utils_requiredField()._(':').'</td><td>';

	$params['name'] = 'htmlcode';
	$params['body'] = $linkValues['htmlcode'];
	$params['width'] = "800";
	$params['height'] = "500";
	$params['content'] = '<textarea name="htmlcode" rows="5" cols="80">'.$params['body'].'</textarea>';
	plugin_hook_by_reference("text_editor", $params);
	echo $params['content'];

	echo '</td></tr>'."\n".'<tr id="urlcode" style="display:none" >';
	echo '<td>'._('URL').utils_requiredField()._(':').'</td><td><input name="link" type="text" maxlength="255" value="'.$linkValues['url'].'" /></td>';
	echo '</tr>'."\n";
	echo '</table>';
	echo '<p>';
	echo '<input type="hidden" name="linkid" value="'.$linkId.'" />';
	echo '<input type="submit" value="'. _('Update') .'" />';
    echo '<input type="submit" name="submit" value="'. _('Cancel') .'" formnovalidate="formnovalidate" />';
	echo '</p>';
	echo '</form>';
	echo utils_requiredField().' '._('Indicates required fields.');
} else {
	$error_msg = _('Cannot retrieve value for this link:').' '.$linkId;
	session_redirect($redirect_url.'&error_msg='.urlencode($error_msg));
}
