<?php
/**
 * FusionForge system action queue
 *
 * Copyright (C) 2014, 2015  Inria (Sylvain Beucler)
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

# Default category (plugin_id field)
define('SYSTASK_CORE', null);

class SysTasksQ extends FFError {
	function add($plugin_id, $systask_type, $group_id, $user_id=null) {
		// Trim duplicate requests, e.g. SCM_REPO ones
		$qpa = db_construct_qpa();
		$qpa = db_construct_qpa($qpa, 'SELECT * FROM systasks WHERE status=$1', array('TODO'));
		$qpa = db_construct_qpa($qpa, ' AND systask_type=$1', array($systask_type));
		if ($plugin_id == null) $qpa = db_construct_qpa($qpa, ' AND plugin_id IS NULL');
		else                    $qpa = db_construct_qpa($qpa, ' AND plugin_id=$1', array($plugin_id));
		if ($group_id == null)  $qpa = db_construct_qpa($qpa, ' AND group_id IS NULL');
		else                    $qpa = db_construct_qpa($qpa, ' AND group_id=$1', array($group_id));
		if ($user_id == null)   $qpa = db_construct_qpa($qpa, ' AND user_id IS NULL');
		else                    $qpa = db_construct_qpa($qpa, ' AND user_id=$1', array($user_id));
		$res = db_query_qpa($qpa);
		if (!$res) {
			$this->setError(sprintf(_('Error: Cannot create system action: %s'),
									db_error()));
			return false;
		}
		if (db_numrows($res) >= 1)
			return true;
		
		$res = db_query_params(
			'INSERT INTO systasks (
				plugin_id,
				systask_type,
				group_id,
				user_id,
				requested
			) VALUES ($1, $2, $3, $4, now())',
			array($plugin_id, $systask_type, $group_id, $user_id));
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
