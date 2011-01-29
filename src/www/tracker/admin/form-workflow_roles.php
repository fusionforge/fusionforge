<?php
/**
 * Workflow Form
 *
 * Copyright 2010, FusionForge Team
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org
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


require_once('common/tracker/ArtifactWorkflow.class.php');

		$from = getIntFromRequest('from');
		$next = getIntFromRequest('next');
		
//	FORM TO UPDATE ARTIFACT TYPES

		/*
			List of possible user built Selection Boxes for an ArtifactType
		*/
	$efarr = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS));
    	if (count($efarr) === 0) {
    		// TODO: Normal status is not implemented right now.
      		return false;
    	} elseif (count($efarr) !== 1) {
			// Internal error.
			return false;
    	}

    	$keys=array_keys($efarr);
    	$field_id = $keys[0];
    	
    	$atw = new ArtifactWorkflow($ath, $field_id);
		$roles = $atw->getAllowedRoles($from, $next);
		
		$elearray = $ath->getExtraFieldElements($field_id);
		foreach ($elearray as $e) {
			$name[ $e['element_id'] ] = $e['element_name'];
		}

		$title = sprintf(_('Configuring allowed roles for the transitions from %1$s to %2$s'), $name[$from], $name[$next]);
		$ath->adminHeader(array ('title'=>$title,'pagename'=>'tracker_admin_customize_liste','titlevals'=>array($ath->getName())));
?>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="field_id" value="<?php echo $field_id ?>" />
		<input type="hidden" name="workflow_roles" value="1" />
		<input type="hidden" name="from" value="<?php echo $from ?>" />
		<input type="hidden" name="next" value="<?php echo $next ?>" />
    	
<?php 
		$group_roles = $group->getRoles() ;
		sortRoleList ($group_roles) ;
		foreach ($group_roles as $role) {
			$value = in_array($role->getID(), $roles)? ' checked="checked"' : '';
			$str = '<input type="checkbox" name="role['.$role->getID().']"'.$value.' />';
			$str .= ' '.$role->getDisplayableName($group);
			echo $str."<br />\n";
		}
?>		
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
		</form>
		<?php

		$ath->footer(array());

?>
