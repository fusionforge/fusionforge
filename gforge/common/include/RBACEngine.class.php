<?php
/**
 * FusionForge RBAC engine
 *
 * Copyright 2010, Roland Mas
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published
 * by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 * 
 * FusionForge is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307
 * USA
 */

require_once $gfcommon.'include/RBAC.php' ;

class RBACEngine extends Error implements PFO_RBACEngine {
	private static $_instance ;

	public static function getInstance() {
		if (!isset(self::$_instance)) {
			$c = __CLASS__;
			self::$_instance = new $c;
		}
		
		return self::$_instance;
	}

	public function getAvailableRoles() {
		$result = array () ;

		$result[] = RoleAnonymous::getInstance() ;
		
		if (session_loggedin()) {
			$result[] = RoleLoggedIn::getInstance() ;

                        $user = session_get_user() ;
			$groups = $user->getGroups() ;
			foreach ($groups as $g) {
				$result[] = $user->getRole($g) ;
			}
		}
		
		return $result ;
	}

	public function getAvailableRolesForUser($user) {
		$result = array () ;

		$result[] = RoleAnonymous::getInstance() ;
		$result[] = RoleLoggedIn::getInstance() ;
		
		$groups = $user->getGroups() ;
		foreach ($groups as $g) {
			$result[] = $user->getRole($g) ;
		}
		
		return $result ;
	}

	public function isActionAllowed ($section, $reference, $action = NULL) {
		$rlist = $this->getAvailableRoles () ;
		foreach ($rlist as $r) {
			if ($r->hasPermission ($section, $reference, $action)) {
				return true ;
			}
		}
		return false ;
	}

	public function isGlobalActionAllowed ($section, $action = NULL) {
		return $this->isActionAllowed ($section, -1, $action) ;
	}

	public function isActionAllowedForUser ($user, $section, $reference, $action = NULL) {
		$rlist = $this->getAvailableRolesForUser ($user) ;
		foreach ($rlist as $r) {
			if ($r->hasPermission ($user, $section, $reference, $action)) {
				return true ;
			}
		}
		return false ;
	}

	public function isGlobalActionAllowedForUser ($user, $section, $action = NULL) {
		return $this->isActionAllowedForUser ($user, $section, -1, $action) ;
	}
}

function forge_check_perm ($section, $reference, $action = NULL) {
	$engine = RBACEngine::getInstance() ;

	return $engine->isActionAllowed($section, $reference, $action) ;
}

function forge_check_global_perm ($section, $action = NULL) {
	$engine = RBACEngine::getInstance() ;

	return $engine->isGlobalActionAllowed($section, $action) ;
}

function forge_check_perm_for_user ($user, $section, $reference, $action = NULL) {
	$engine = RBACEngine::getInstance() ;

	return $engine->isActionAllowedForUser($user, $section, $reference, $action) ;
}

function forge_check_global_perm_for_user ($user, $section, $action = NULL) {
	$engine = RBACEngine::getInstance() ;

	return $engine->isGlobalActionAllowedForUser($user, $section, $action) ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
