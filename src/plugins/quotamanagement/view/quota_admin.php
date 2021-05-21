<?php
/**
 * Project Admin page to manage quotas disk and database
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011,2016, Franck Villaume - Capgemini
 * Copyright 2019,2021, Franck Villaume - TrivialDev
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

$cmd = getStringFromRequest('cmd');

$quotamanagement = plugin_get_object('quotamanagement');

$_quota_block_size = intval(trim(shell_exec('echo $BLOCK_SIZE'))) + 0;
if ($_quota_block_size == 0) {
	$_quota_block_size = 1024;
}

$subMenuTitle = array();
$subMenuUrl = array();
$subMenuAttr = array();
$subMenuTitle[] = _('Ressources usage and quota');
$subMenuUrl[] = '/plugins/'.$quotamanagement->name.'/?type=globaladmin';
$subMenuAttr[] = array('title' => _('View quota and usage per project and user.'));
$subMenuTitle[] = _('Admin');
$subMenuUrl[] = '/plugins/'.$quotamanagement->name.'/?type=globaladmin&view=admin';
$subMenuAttr[] = array('title' => _('Administrate quotas per project.'));
echo $HTML->subMenu($subMenuTitle, $subMenuUrl, $subMenuAttr);

// stock projects infos in array
$quotas = array();

// all projects list
$res_db = db_query_params('SELECT plugin_quotamanagement.*, groups.group_name, groups.unix_group_name FROM plugin_quotamanagement, groups
			WHERE plugin_quotamanagement.group_id = groups.group_id ORDER BY group_id',
			array());
if (db_numrows($res_db) > 0) {
	while($e = db_fetch_array($res_db)) {
		$qh = $e["quota_hard"] / $_quota_block_size;
		$qs = $e["quota_soft"] / $_quota_block_size;
		$quotas["$e[group_id]"]["group_id"] = $e["group_id"];
		$quotas["$e[group_id]"]["name"] = $e["group_name"];
		$quotas["$e[group_id]"]["unix_name"] = $e["unix_group_name"];
		$quotas["$e[group_id]"]["database_size"] = 0;
		$quotas["$e[group_id]"]["disk_size"] = 0;
		$quotas["$e[group_id]"]["quota_hard"] = $qh;
		$quotas["$e[group_id]"]["quota_soft"] = $qs;
		$quotas["$e[group_id]"]["quota_db_hard"] = $e["quota_db_hard"];
		$quotas["$e[group_id]"]["quota_db_soft"] = $e["quota_db_soft"];
	}
}

echo html_e('h2', array(), _('Projects Quota'));
$titleArray = array(_('id'), _('unixname'), _('name'), _('database quota soft').' (MB)', _('database quota hard').' (MB)', _('disk quota soft').' (MB)', _('disk quota hard').' (MB)', '');
$thClassArray = array('', '', '', 'align-right unsortable', 'align-right unsortable', 'align-right unsortable', 'align-right unsortable', 'unsortable');
echo $HTML->listTableTop($titleArray, array(), 'sortable', 'sortable_quota', $thClassArray);
foreach ($quotas as $q) {
	$cells = array();
	$cells[][] = $q['group_id'];
	$cells[][] = util_make_link('/plugins/'.$quotamanagement->name.'/?group_id='.$q['group_id'].'&type=projectadmin', $q['unix_name']);
	$cells[][] = $q['name'];
	$cells[] = array($HTML->html_input('qds', '', '', 'numeric', $q['quota_db_soft'], array('class' => 'align-right', 'form' => 'q'.$group_id, 'min' => 0)));
	$cells[] = array($HTML->html_input('qdh', '', '', 'numeric', $q['quota_db_hard'], array('class' => 'align-right', 'form' => 'q'.$group_id, 'min' => 0)));
	$cells[] = array($HTML->html_input('qs', '', '', 'numeric', $q['quota_soft'], array('class' => 'align-right', 'form' => 'q'.$group_id, 'min' => 0)));
	$cells[] = array($HTML->html_input('qh', '', '', 'numeric', $q['quota_hard'], array('class' => 'align-right', 'form' => 'q'.$group_id, 'min' => 0)));
	$cells[] = array($HTML->openForm(array('action' => '/plugins/'.$quotamanagement->name.'/?type=globaladmin&action=update', 'method' => 'post', 'id' => 'q'.$group_id))
			.$HTML->html_input('submit', '', '', 'submit', _('Modify'), array('form' => 'q'.$group_id))
			.$HTML->html_input('group_id', '', '', 'hidden', $q['group_id'], array('form' => 'q'.$group_id))
			.$HTML->closeForm());
	echo $HTML->multiTableRow(array(), $cells);
}
echo $HTML->listTableBottom();
site_admin_footer();
