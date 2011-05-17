<?php
/**
 * API for role-based access control
 * Defined at Planetforge.org
 *
 * Copyright 2010, Roland Mas
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

// Constants to identify role classes
define ("PFO_ROLE_EXPLICIT",  1) ;
define ("PFO_ROLE_ANONYMOUS", 2) ;
define ("PFO_ROLE_LOGGEDIN",  3) ;
define ("PFO_ROLE_UNION",     4) ;

// Interface for the RBAC engine
interface PFO_RBACEngine {
	public static function getInstance() ;
	public function getAvailableRoles() ; // From session
	public function isActionAllowed($section, $reference, $action = NULL) ;
	public function isGlobalActionAllowed($section, $action = NULL) ;
	public function isActionAllowedForUser($user, $section, $reference, $action = NULL) ;
	public function isGlobalActionAllowedForUser($user, $section, $action = NULL) ;
	public function getRolesByAllowedAction($section, $reference, $action = NULL) ;
	public function getUsersByAllowedAction($section, $reference, $action = NULL) ;
}

// Interfaces for the capabilities
interface PFO_Role {
	public function getName() ;
	public function setName($name) ;
	public function getID() ;

	public function isPublic() ;
	public function setPublic($flag) ;
	public function getHomeProject() ;
	public function getLinkedProjects() ;
	public function linkProject($project) ;
	public function unlinkProject($project) ;

	public function getUsers() ;
	public function hasUser($user) ;
	public function hasPermission($section, $reference, $action = NULL) ;
	public function hasGlobalPermission($section, $action = NULL) ;
	public function normalizeData() ;
	public function getSettings() ;
	public function getSettingsForProject($project) ;
	public function setSettings($data) ;
}

interface PFO_RoleExplicit extends PFO_Role {
	const roleclass = PFO_ROLE_EXPLICIT ;
	public function addUsers($users) ;
	public function removeUsers($users) ;
}

interface PFO_RoleUnion extends PFO_Role {
	const roleclass = PFO_ROLE_UNION ;
	public function addRole($role) ;
	public function removeRole($role) ;
}

interface PFO_RoleAnonymous extends PFO_Role {
	const roleclass = PFO_ROLE_ANONYMOUS ;
}

interface PFO_RoleLoggedin extends PFO_Role {
	const roleclass = PFO_ROLE_LOGGEDIN ;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
