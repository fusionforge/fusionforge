<?php
/*
 * Copyright 2010 Roland Mas
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
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function role_box ($group_id,$name,$selected='xzxzxz',$local_only=true) {
	$group = group_get_object ($group_id) ;
	$roles = $group->getRoles () ;
	
	if ($local_only) {
		$roles2 = array () ;
		foreach ($roles as $role) {
			$hp = $role->getHomeProject() ;
			if (($hp != NULL) && ($hp->getID() == $group_id)) {
				$roles2[] = $role ;
			}
		}
		$roles = $roles2 ;
	}

	$ids = array () ;
	$names = array () ;
	
	foreach ($roles as $role) {
		$ids[] = $role->getID ();

		$names[] = $role->getDisplayableName($group) ;
	}

	if ($selected == 'xzxzxz') {
		$selected = $ids[0] ;
	}

	return html_build_select_box_from_arrays($ids,$names,$name,$selected,false,'',false);
}

function external_role_box ($group_id,$name) {
	$group = group_get_object ($group_id) ;
	$roles = array () ;
	foreach (RBACEngine::getInstance()->getPublicRoles() as $r) {
		$grs = $r->getLinkedProjects () ;
		$seen = false ;
		foreach ($grs as $g) {
			if ($g->getID() == $group_id) {
				$seen = true ;
				break ;
			}
		}
		if (!$seen) {
			$roles[] = $r ;
		}
	}

	$ids = array () ;
	$names = array () ;
	foreach ($roles as $role) {
		$ids[] = $role->getID ();

		$names[] = $role->getDisplayableName($group) ;
	}

	$selected = $ids[0] ;

	return html_build_select_box_from_arrays($ids,$names,$name);
}

function global_role_box ($name,$selected='xzxzxz') {
	$roles = RBACEngine::getInstance()->getGlobalRoles () ;

	$ids = array () ;
	$names = array () ;
	
	foreach ($roles as $role) {
		$ids[] = $role->getID ();
		
		$names[] = $role->getName () ;
	}

	if ($selected == 'xzxzxz') {
		$selected = $ids[0] ;
	}

	return html_build_select_box_from_arrays($ids,$names,$name,$selected,false,'',false);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
