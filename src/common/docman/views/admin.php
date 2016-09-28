<?php
/**
 * FusionForge Documentation Manager
 *
 * Copyright 2000, Quentin Cregan/Sourceforge
 * Copyright 2002-2003, Tim Perdue/GForge, LLC
 * Copyright 2010-2011, Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2014-2015, Franck Villaume - TrivialDev
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
global $g; // Group object
global $group_id; // id of the group
global $warning_msg;
global $HTML;

if ( !forge_check_perm('docman', $group_id, 'admin')) {
	$warning_msg = _('Document Manager Access Denied');
	session_redirect('/docman/?group_id='.$group_id);
}

echo html_ao('div', array('id' => 'principalAdminDiv', 'class' => 'docmanDivIncluded'));
echo html_ao('script', array('type' => 'text/javascript'));
?>
//<![CDATA[
function doIt(formid) {
	document.getElementById(formid).submit();
	document.getElementById('submit'+formid).disabled = true;
}
//]]>
<?php
echo html_ac(html_ap() - 1);
if (extension_loaded('zip')) {
	echo $HTML->openForm(array('id' => 'backup', 'name' => 'backup', 'method' => 'post', 'action' => '/docman/view.php/'.$group_id.'/backup'));
	echo html_ao('ul');
	echo html_e('li', array(), html_e('input', array('id' => 'submitbackup', 'type' => 'button', 'value' => _('Extract documents and directories as an archive'), 'onclick' => 'javascript:doIt("backup")')), false);
	echo html_ac(html_ap() -1);
	echo $HTML->closeForm();
}

echo $HTML->openForm(array('id' => 'createonline', 'name' => 'createonline', 'method' => 'post', 'action' => '/docman/?group_id='.$group_id.'&action=updatecreateonline'));
echo html_ao('ul');
$createOnlineStatus = '1';
$labelCreateOnline = _('Enable Create Online Documents');
if ($g->useCreateOnline()) {
	$createOnlineStatus='0';
	$labelCreateOnline = _('Disable Create Online Documents');
}
echo html_e('li', array(), html_e('input', array('name' => 'status', 'type' => 'hidden', 'value' => $createOnlineStatus)).html_e('input', array('id' => 'submitcreateonline', 'type' => 'button', 'value' => $labelCreateOnline, 'onclick' => 'javascript:doIt("createonline")')), false);
echo html_ac(html_ap() -1);
echo $HTML->closeForm();

echo $HTML->openForm(array('id' => 'searchengine', 'name' => 'searchengine', 'method' => 'post', 'action' => '/docman/?group_id='.$group_id.'&action=updateenginesearch'));
echo html_ao('ul');
$searchEngineStatus = '1';
$labelSearchEngine = _('Enable Search Engine');
if ($g->useDocmanSearch()) {
	$searchEngineStatus='0';
	$labelSearchEngine = _('Disable Search Engine');
}
echo html_e('li', array(), html_e('input', array('name' => 'status', 'type' => 'hidden', 'value' => $searchEngineStatus)).html_e('input', array('id' => 'submitsearchengine', 'type' => 'button', 'value' => $labelSearchEngine, 'onclick' =>'javascript:doIt("searchengine")')), false);
echo html_ac(html_ap() -1);
echo $HTML->closeForm();

if ($g->useDocmanSearch()) {
	echo $HTML->openForm(array('id' => 'reindexword', 'name' => 'reindexword', 'method' => 'post', 'action' => '/docman/?group_id='.$group_id.'&action=forcereindexenginesearch'));
	echo html_ao('ul');
	echo html_e('li', array(), html_e('input', array('name' => 'status', 'type' => 'hidden', 'value' => '1')).html_e('input', array('id' => 'submitreindexword', 'type' => 'button', 'value' => _('Force reindexation search engine'), 'onclick' => 'javascript:doIt("reindexword")')), false);
	echo html_ac(html_ap() -1);
	echo $HTML->closeForm();
}

if (forge_get_config('use_webdav')) {
	echo $HTML->openForm(array('id' => 'webdavinterface', 'name' => 'webdavinterface', 'method' => 'post', 'action' => '/docman/?group_id='.$group_id.'&action=updatewebdavinterface'));
	echo html_ao('ul');
	$webdavStatus = '1';
	$labelWebdavInterface = _('Enable Webdav Interface');
	if ($g->useWebdav()) {
		$webdavStatus = '0';
		$labelWebdavInterface = _('Disable Webdav Interface');
	}
	echo html_e('li', array(), html_e('input', array('name' => 'status', 'type' => 'hidden', 'value' => $webdavStatus)).html_e('input', array('id' => 'submitweddavinterface', 'type' => 'button', 'value' => $labelWebdavInterface, 'onclick' => 'javascript:doIt("webdavinterface")')), false);
	echo html_ac(html_ap() -1);
	echo $HTML->closeForm();
}

plugin_hook('hierarchy_views', array($group_id, 'docman'));
echo html_ac(html_ap() -1);
