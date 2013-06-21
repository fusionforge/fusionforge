<?php
/**
 * FusionForge system authentication management
 *
 * Copyright 2012, Roland Mas
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

abstract class SysAuthPlugin extends Plugin {
        function SysAuthPlugin() {
                $this->Plugin();
		$this->_addHook("user_create");
		$this->_addHook("user_update");
		$this->_addHook("user_delete");
		$this->_addHook("user_setstatus");
		$this->_addHook("user_setemail");
		$this->_addHook("user_setpasswd");
		$this->_addHook("group_create");
		$this->_addHook("group_update");
		$this->_addHook("group_delete");
		$this->_addHook("group_adduser");
		$this->_addHook("group_removeuser");
		$this->_addHook("group_setstatus");
		$this->_addHook("group_approved");
		$this->_addHook("group_update_members");
        }

        function CallHook ($hookname, &$params) {
		$this->$hookname($params);
        }

	abstract function user_update($params);
	abstract function user_delete($params);
	abstract function group_update($params);
	abstract function group_delete($params);

	function user_create($params) {
		return $this->user_update($params);
	}
	function user_setstatus($params) {
		return $this->user_update($params);
	}
	function user_setemail($params) {
		return $this->user_update($params);
	}
	function user_setpasswd($params) {
		return $this->user_update($params);
	}

	function group_create($params) {
		return $this->group_update($params);
	}
	function group_adduser($params) {
		return $this->group_update_members($params);
	}
	function group_removeuser($params) {
		return $this->group_update_members($params);
	}
	function group_setstatus($params) {
		return $this->group_update($params);
	}
	function group_approved($params) {
		return $this->group_update($params);
	}
	function group_update_members($params) {
		return $this->group_update($params);
	}

}
