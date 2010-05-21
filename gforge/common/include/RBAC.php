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

require "PFO-RBAC.interface.php" ;

// Code shared between classes

abstract class BaseRole extends Error implements PFO_Role {

	public function getUsers() {
		return array () ;
	}
	public function hasUser($user) {
		return false ;
	}
	public function hasPermission($section, $reference, $permission) {
		return false ;
	}
	public function getSettings() {
		return array () ;
	}
	public function setSettings($data) {
		return true ;
	}
}

// Actual classes

abstract class RoleExplicit extends BaseRole implements PFO_RoleExplicit {
	public function addUsers($users) {
		throw new Exception ("Not implemented") ;
	}
	public function removeUsers($users) {
		throw new Exception ("Not implemented") ;
	}
	public function getUsers() {
		throw new Exception ("Not implemented") ;
	}
}

class RoleAnonymous extends BaseRole implements PFO_RoleAnonymous {
	public function getID () {
		return -PFO_ROLE_ANONYMOUS ;
	}
	public function isPublic () {
		return true ;
	}
	public function setPublic ($flag) {
		throw new Exception ("Can't setPublic() on RoleAnonymous") ;
	}
	public function getHomeProject () {
		return NULL ;
	}
	public function getLinkedProjects () {
		throw new Exception ("Not implemented") ;
	}
	public function linkProject ($project) {
		throw new Exception ("Not implemented") ;
	}
	public function unlinkProject ($project) {
		throw new Exception ("Not implemented") ;
	}
	public function normalizeData () {
		throw new Exception ("Not implemented") ;
	}
	public function getName () {
		return _('Anonymous/not logged in') ;
	}
	public function setName ($name) {
		throw new Exception ("Can't setName() on RoleAnonymous") ;
	}
}

class RoleLoggedIn extends BaseRole implements PFO_RoleLoggedIn {
	public function getID () {
		return -PFO_ROLE_LOGGEDIN ;
	}
	public function isPublic () {
		return true ;
	}
	public function setPublic ($flag) {
		throw new Exception ("Can't setPublic() on RoleLoggedIn") ;
	}
	public function getHomeProject () {
		return NULL ;
	}
	public function getLinkedProjects () {
		throw new Exception ("Not implemented") ;
	}
	public function linkProject ($project) {
		throw new Exception ("Not implemented") ;
	}
	public function unlinkProject ($project) {
		throw new Exception ("Not implemented") ;
	}
	public function normalizeData () {
		throw new Exception ("Not implemented") ;
	}
	public function getName () {
		return _('Any user logged in') ;
	}
	public function setName ($name) {
		throw new Exception ("Can't setName() on RoleLoggedIn") ;
	}
}

abstract class RoleUnion extends BaseRole implements PFO_RoleUnion {
	public function addRole ($role) {
		return true ;
	}
	public function removeRole ($role) {
		return true ;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
