<?php
/**
 * FusionForge system action queue
 *
 * Copyright (C) 2014  Inria (Sylvain Beucler)
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

define('SYSTASK_CORE', null);
define('SYSTASK_HOMEDIR', 25);
define('SYSTASK_SCM_REPO', 27);

class SysTasksQ extends Error {
		function add($plugin_id, $systask_type_id, $group_id, $user_id=null) {
				$res = db_query_params('INSERT INTO systasks (
				    plugin_id,
				    systask_type_id,
				    group_id,
				    user_id,
				    requested
				  ) VALUES ($1, $2, $3, $4, now())',
				  array($plugin_id, $systask_type_id, $group_id, $user_id));
				if (!$res || db_affected_rows($res) < 1) {
						$this->setError(sprintf(_('Error: Cannot create system action: %s'),
												db_error()));
						return false;
				}
		}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
