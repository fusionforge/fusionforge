<?php
/**
 * API for role-based access control
 * Defined at Planetforge.org
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

// Constants for roles' “capabilities”
define ("PFO_ROLE_CAP_EXPLICIT",  1) ;
define ("PFO_ROLE_CAP_FORGEWIDE", 2) ;
define ("PFO_ROLE_CAP_UNION",     4) ;
define ("PFO_ROLE_CAP_ANONYMOUS", 8) ;
define ("PFO_ROLE_CAP_LOGGEDIN", 16) ;

// Constants to identify role classes
define ("PFO_ROLE_STANDARD", PFO_ROLE_CAP_EXPLICIT) ;
define ("PFO_ROLE_GLOBAL", PFO_ROLE_CAP_EXPLICIT | PFO_ROLE_CAP_FORGEWIDE) ;
define ("PFO_ROLE_ANONYMOUS", PFO_ROLE_CAP_FORGEWIDE | PFO_ROLE_CAP_ANONYMOUS) ;
define ("PFO_ROLE_LOGGEDIN", PFO_ROLE_CAP_FORGEWIDE | PFO_ROLE_CAP_LOGGEDIN) ;
define ("PFO_ROLE_UNIONPROJECT", PFO_ROLE_CAP_UNION) ;
define ("PFO_ROLE_UNIONGLOBAL", PFO_ROLE_CAP_FORGEWIDE | PFO_ROLE_CAP_UNION) ;

// Interfaces for the capabilities
interface PFO_BaseRole {
	public function getName() ;
	public function setName() ;
	public function getID() ;
	public function getUsers() ;
	public function hasUser($user) ;
	public function hasPermission($section, $reference, $permission) ;
	public function normalizeData() ;
	public function getSettings() ;
	public function setSettings($data) ;
	public function getLinkedProjects() ;
}

interface PFO_RoleExplicit extends PFO_BaseRole {
	public function addUser($user) ;
	public function removeUser($user) ;
}

interface PFO_RoleForgeWide extends PFO_BaseRole {
	public function linkProject($project) ;
	public function unlinkProject($project) ;

}

interface PFO_RoleUnion extends PFO_BaseRole {
	public function addRole($role) ;
	public function removeRole($role) ;
}

// Interfaces for the combination of capabilities

interface PFO_RoleStandard extends PFO_RoleExplicit {
	const role_caps = PFO_ROLE_STANDARD ;
}

interface PFO_RoleGlobal extends PFO_RoleExplicit {
	const role_caps = PFO_ROLE_GLOBAL ;
}

interface PFO_RoleAnonymous extends PFO_RoleForgeWide {
	const role_caps = PFO_ROLE_ANONYMOUS ;
}

interface PFO_RoleLoggedin extends PFO_RoleForgeWide {
	const role_caps = PFO_ROLE_LOGGEDIN ;
}

interface PFO_RoleUnionProject extends PFO_RoleUnion {
	const role_caps = PFO_ROLE_UNIONPROJECT ;
}

interface PFO_RoleUnionGlobal extends PFO_RoleForgeWide, PFO_RoleUnion {
	const role_caps = PFO_ROLE_UNIONGLOBAL ;
}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
