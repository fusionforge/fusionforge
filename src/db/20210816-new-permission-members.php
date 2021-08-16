<?php
/**
 * Inject new permission "members" to existing roles.
 * Copyright, 2021, Franck Villaume - TrivialDev
 * http://fusionforge.org/
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

require_once dirname(__FILE__).'/../common/include/env.inc.php';
require_once $gfcommon.'include/pre.php';


ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);

$roleids = db_query_params('select role_id from pfo_role_setting group by role_id order by role_id');
while ($roleid = db_fetch_array($roleids)) {
	db_query_params('insert into pfo_role_setting (role_id, section_name, perm_val) VALUES ($1, \'members\', 1)', array($roleid['role_id']));
}
echo "SUCCESS\n";
exit(0);
