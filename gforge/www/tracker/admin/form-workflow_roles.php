<?php

require_once('common/tracker/ArtifactWorkflow.class.php');

		$from = getIntFromRequest('from');
		$next = getIntFromRequest('next');
		
		//
//	FORM TO UPDATE ARTIFACT TYPES
//
		$ath->adminHeader(array ('title'=> _('Configure allowed roles'),'pagename'=>'tracker_admin_customize_liste','titlevals'=>array($ath->getName())));

		/*
			List of possible user built Selection Boxes for an ArtifactType
		*/
		$efarr =& $ath->getExtraFields(ARTIFACT_EXTRAFIELDTYPE_STATUS);
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

?>
    	<h1><?php printf(_('Configuring allowed roles for the transitions from %1$s to %2$s'), $name[$from], $name[$next]) ?></h1>
 		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="field_id" value="<?php echo $field_id ?>" />
		<input type="hidden" name="workflow_roles" value="1" />
		<input type="hidden" name="from" value="<?php echo $from ?>" />
		<input type="hidden" name="next" value="<?php echo $next ?>" />
    	
<?php 
		$res=db_query_params ('SELECT role_id,role_name 
			FROM role WHERE group_id=$1 ORDER BY role_name',
			array($group_id));
		while($arr = db_fetch_array($res)) {
			$value = in_array($arr['role_id'], $roles)? ' checked="checked"' : '';
			$str = '<input type="checkbox" name="role['.$arr['role_id'].']"'.$value.' />';
			$str .= ' '.$arr['role_name'];
			echo $str."<br />\n";
		}
?>		
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
		</form>
		<?php

		$ath->footer(array());

?>
