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

global $quotamanagement;
global $group_id;

if ( !forge_check_global_perm('forge_admin')) {
	$warning_msg = _('Quota Management Action Denied.');
	session_redirect('/plugins/'.$quotamanagement->name.'/?group_id='.$group_id, false);
}

require_once $gfcommon.'include/SysTasksQ.class.php';

$_quota_block_size = intval(trim(shell_exec('echo $BLOCK_SIZE')));
if ($_quota_block_size == 0) {
	$_quota_block_size = 1024;
}

$type = getStringFromRequest('type');
$qs = getIntFromRequest('qs', 0) * $_quota_block_size;
$qh = getIntFromRequest('qh', 0) * $_quota_block_size;
$qds = getIntFromRequest('qds', 0) * $_quota_block_size;
$qdh = getIntFromRequest('qdh', 0) * $_quota_block_size;

if ($qs > $qh || $qds > $qdh) {
	$error_msg = _('Input error: Hard quota must be greater than soft quota');
} else {
	db_query_params('UPDATE plugin_quotamanagement SET quota_soft = $1, quota_hard = $2, quota_db_soft = $3, quota_db_hard = $4 WHERE group_id = $5',
			array($qs, $qh, $qds, $qdh, $group_id));
	$systasksq = new SysTasksQ();
	$systasksq->add($quotamanagement->getID(), 'QUOTAMANAGEMENT_SET_QUOTA', $group_id);
	$feedback = _('Quota updated successfully');
}

$redirect_url = '/plugins/'.$quotamanagement->name.'/?type='.$type;
session_redirect($redirect_url, false);
