<?php
/**
 * FusionForge role-based access control
 *
 * Copyright 2004, GForge, LLC
 * Copyright 2009-2010, Roland Mas
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

require "PFO-RBAC.php" ;

// Code shared between classes

abstract class Error {}

abstract class BaseRole extends Error implements PFO_BaseRole {
	public function getName() {
		return $this->name ;
	}
	public function setName() {
		return true ;
	}
	public function getID() {
		return $this->ID ;
	}
	public function getUsers() {
		return array () ;
	}
	public function hasUser($user) {
		return false ;
	}
	public function hasPermission($section, $reference, $permission) {
		return false ;
	}
	public function normalizeData() {
		return true ;
	}
	public function getSettings() {
		return array () ;
	}
	public function setSettings($data) {
		return true ;
	}
	public function getLinkedProjects() {
		return array () ;
	}
}

abstract class BaseRoleGlobal extends BaseRole implements PFO_RoleForgeWide {
	public function linkProject($project) {
		return true ;
	}
	public function unlinkProject($project) {
		return true ;
	}
}

// Actual classes

class RoleStandard extends BaseRole implements PFO_RoleStandard {
	public function addUser($user) {
		return true ;
	}
	public function removeUser($user) {
		return true ;
	}
	public function getUsers() {
		return array () ;
	}
	public function getProject() {
		return false ;
	}
}

class RoleGlobal extends BaseRoleGlobal implements PFO_RoleGlobal {
	public function addUser($user) {
		return true ;
	}
	public function removeUser($user) {
		return true ;
	}
}

class RoleAnonymous extends BaseRoleGlobal implements PFO_RoleAnonymous {
	public function getName () {
		return _('Anonymous/not logged in') ;
	}
	public function setName ($name) {
		throw new Exception ("Can't setName() on RoleAnonymous") ;
	}
}

class RoleLoggedIn extends BaseRoleGlobal implements PFO_RoleLoggedIn {
	public function getName () {
		return _('Any user logged in') ;
	}
	public function setName ($name) {
		throw new Exception ("Can't setName() on RoleLoggedIn") ;
	}
}

class RoleUnionProject extends BaseRole implements PFO_RoleUnionProject {
	public function addRole ($role) {
		return true ;
	}
	public function removeRole ($role) {
		return true ;
	}
}

class RoleUnionGlobal extends BaseRoleGlobal implements PFO_RoleUnionGlobal {
	public function addRole ($role) {
		return true ;
	}
	public function removeRole ($role) {
		return true ;
	}
}

$rs = new RoleStandard () ;
$rg = new RoleGlobal () ;
$ra = new RoleAnonymous () ;
$rl = new RoleLoggedIn () ;
$rup = new RoleUnionProject () ;
$rug = new RoleUnionGlobal () ;

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
