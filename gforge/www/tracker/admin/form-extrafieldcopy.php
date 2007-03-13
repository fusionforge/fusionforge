<?php

//
//  FORM TO COPY Choices configured by admin for extra_field BOXES 
//
		$id = getIntFromRequest('id');
		$fb= new ArtifactExtraField($ath,$id);
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_copy','choices_title',$fb->getName())));
		echo "<h3>".$Language->getText('tracker_admin_copy','choices_title',$fb->getName())."</h3>";
		
		$efearr =& $ath->getExtraFieldElements($id);
		for ($i=0; $i<count($efearr); $i++) {
			$field_id_arr[] = $efearr[$i]['element_id'];
			$field_arr[] = $efearr[$i]['element_name'];
		}
		echo '<table>';
		echo '<tr>';
		echo '<td></td><td><center><strong>';
		echo _('Copy From');
		echo '<br />';
		echo $fb->getName();
		echo '</center></strong></td><td></td><td><strong><center>';
		
		echo _('Into trackers and custom fields');
		echo '</center></strong></tr><tr><td><strong><center>';
		echo '</center></strong></td>';
		echo '<td valign=top>';
		?>
		
		<form action="<?php echo getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post" >
		<input type="hidden" name="copy_opt" value="copy" >
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<?php
		echo html_build_multiple_select_box_from_arrays($field_id_arr,$field_arr,'copyid[]',array(),10,false);
		echo '</td><td><strong><center>';
		//get a list of all extra fields in trackers and groups that you have perms to admin
		$sql="SELECT g.unix_group_name, agl.name AS tracker_name, aefl.field_name, aefl.extra_field_id
			FROM groups g, 
			artifact_group_list agl, 
			artifact_extra_field_list aefl,
			user_group ug,
			artifact_perm ap
			WHERE 
			(ug.admin_flags='A' OR ug.artifact_flags='2' OR ap.perm_level>='2')
			AND ug.user_id='".user_getid()."'
			AND ug.group_id=g.group_id
			AND g.group_id=agl.group_id 
			AND agl.group_artifact_id=ap.group_artifact_id
			AND ap.user_id='".user_getid()."'
			AND aefl.group_artifact_id=agl.group_artifact_id
			AND aefl.field_type IN (1,2,3,5)";
		$res=db_query($sql);

//		echo db_error().$sql;

		while($arr =db_fetch_array($res)) {
				$name_arr[]=$arr['unix_group_name']. '::'. $arr['tracker_name'] . '::'. $arr['field_name'];
				$id_arr[]=$arr['extra_field_id'];
		}
		echo '<td valign=top>';

		echo html_build_select_box_from_arrays($id_arr,$name_arr,'selectid',$selectid,false);
		echo '</td></tr>';
		echo '<tr><td>';
		?>
		<br />
	 	<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
		</td></tr></table></form>
		
		<?php
		$ath->footer(array());

?>
