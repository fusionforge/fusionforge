<?php
/**
 * Project Admin page to manage quotas disk and database
 *
 * Copyright 2005, Fabio Bertagnin
 * Copyright 2011,2016, Franck Villaume - Capgemini
 * Copyright 2019, Franck Villaume - TrivialDev
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

global $quota_management;
global $group_id;

$_quota_block_size = trim(shell_exec('echo $BLOCK_SIZE')) + 0;
if ($_quota_block_size == 0) $_quota_block_size = 1024;

$type = getStringFromRequest('type');

$qs = $_POST["qs"] * $_quota_block_size;
$qh = $_POST["qh"] * $_quota_block_size;
if ($qs > $qh) {
	$error_msg = _('Input error: Hard quota must be greater than soft quota');
} else {
	db_query_params('UPDATE groups SET quota_soft = $1, quota_hard = $2 WHERE group_id = $3',
			array($qs,
				$qh,
				getIntFromRequest('group_id')));
	$feedback = _('Quota updated successfully');
}

$redirect_url = '/plugins/'.$quota_management->name.'/?type='.$type;
session_redirect($redirect_url, false);
