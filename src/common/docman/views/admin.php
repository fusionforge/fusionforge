<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
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

/* please do not add require here : use www/docman/index.php to add require */
/* global variables used */
global $g; //group object
global $group_id; // id of the group

if ( !forge_check_perm ('docman', $group_id, 'admin')) {
	$return_msg= _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id.'&warning_msg='.urlencode($return_msg));
}
?>

<div id="principalAdminDiv" class="docmanDivIncluded">
<script language="JavaScript" type="text/javascript">/* <![CDATA[ */
function doIt(formid) {
	document.getElementById(formid).submit();
	document.getElementById('submit'+formid).disabled = true;
}
/* ]]> */</script>
<?php
if (extension_loaded('zip')) {
	echo '<form id="backup" name="backup" method="post" action="'. util_make_uri('/docman/view.php/'.$group_id.'/backup') .'" >';
	echo '<ul>';
	echo '<li><input id="submitbackup" type="button" value="'. _('Extract documents and directories as an archive') .'" onclick="javascript:doIt(\'backup\')"></li>';
	echo '</ul>';
	echo '</form>';
}

echo '<form id="createonline" name="createonline" method="post" action="?group_id='.$group_id.'&amp;action=updatecreateonline" >';
echo '<ul>';
$createOnlineStatus = '1';
$labelCreateOnline = _('Enable Create Online Documents');
if ($g->useCreateOnline()) {
	$createOnlineStatus='0';
	$labelCreateOnline = _('Disable Create Online Documents');
}
echo '<li><input name="status" type="hidden" value="'.$createOnlineStatus.'"><input id="submitcreateonline" type="button" value="'.$labelCreateOnline.'" onclick="javascript:doIt(\'createonline\')"></li>';
echo '</ul>';
echo '</form>';

echo '<form id="searchengine" name="searchengine" method="post" action="?group_id='.$group_id.'&amp;action=updateenginesearch" >';
echo '<ul>';
$searchEngineStatus = '1';
$labelSearchEngine = _('Enable Search Engine');
if ($g->useDocmanSearch()) {
	$searchEngineStatus='0';
	$labelSearchEngine = _('Disable Search Engine');
}
echo '<li><input name="status" type="hidden" value="'.$searchEngineStatus.'"><input id="submitsearchengine" type="button" value="'.$labelSearchEngine.'" onclick="javascript:doIt(\'searchengine\')"></li>';
echo '</ul>';
echo '</form>';

if ($g->useDocmanSearch()) {
	echo '<form id="reindexword" name="reindexword" method="post" action="?group_id='.$group_id.'&amp;action=forcereindexenginesearch">';
	echo '<ul>';
	echo '<li><input name="status" type="hidden" value="1"><input id="submitreindexword" type="button" value="'. _('Force reindexation search engine') .'" onclick="javascript:doIt(\'reindexword\')"></li>';
	echo '</ul>';
	echo '</form>';
}

if (forge_get_config('use_webdav')) {
	echo '<form id="webdavinterface" name="webdavinterface" method="post" action="?group_id='.$group_id.'&amp;action=updatewebdavinterface" >';
	echo '<ul>';
	$webdavStatus = '1';
	$labelWebdavInterface = _('Enable Webdav Interface');
	if ($g->useWebDav()) {
		$webdavStatus = '0';
		$labelWebdavInterface = _('Disable Webdav Interface');
	}
	echo '<li><input name="status" type="hidden" value="'.$webdavStatus.'"><input id="submitweddavinterface" type="button" value="'.$labelWebdavInterface.'" onclick="javascript:doIt(\'webdavinterface\')"></li>';
	echo '</ul>';
	echo '</form>';
}

plugin_hook('hierarchy_views', array($group_id, 'docman'));
echo '</div>';
?>
