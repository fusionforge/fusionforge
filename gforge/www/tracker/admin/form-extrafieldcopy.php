<?php

//
//  FORM TO COPY Choices configured by admin for extra_field BOXES 
//
		$id = getIntFromRequest('id');
		$fb= new ArtifactExtraField($ath,$id);

		// Get a list of all extra fields in trackers and groups that you have perms to admin
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
			AND aefl.extra_field_id != $id
			AND aefl.field_type IN (1,2,3,5,7)";
		$res=db_query($sql);
		if (db_numrows($res) < 1) {
			exit_error('Cannot find a destination tracker where you have administration rights.');
		}
		
		$title = sprintf(_('Copy choices from custom field %1$s'), $fb->getName());
		$ath->adminHeader(array ('title'=>$title));
		echo "<h3>".$title."</h3>";
		
		$efearr =& $ath->getExtraFieldElements($id);
		for ($i=0; $i<count($efearr); $i++) {
			$field_id_arr[] = $efearr[$i]['element_id'];
			$field_arr[] = $efearr[$i]['element_name'];
		}
		?>
		<form action="<?php echo getStringFromServer('PHP_SELF') .'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post" >
                <?php
		echo '<table>';
		echo '<tr>';
		echo '<td></td><td><center><strong>';
		echo _('Copy From');
		echo '<br />';
		echo $fb->getName();
		echo '</strong></center></td><td></td><td><center><strong>';
		
		echo _('Into trackers and custom fields');
		echo '</strong></center></td></tr><tr><td><center><strong>';
		echo '</strong></center></td>';
		echo '<td valign="top">';
		?>
		
		<input type="hidden" name="copy_opt" value="copy" />
		<input type="hidden" name="id" value="<?php echo $id; ?>" />
		<?php
		echo html_build_multiple_select_box_from_arrays($field_id_arr,$field_arr,'copyid[]',array(),10,false);
		echo '</td><td><center><strong>';
		
//		echo db_error().$sql;

		while($arr =db_fetch_array($res)) {
				$name_arr[]=$arr['unix_group_name']. '::'. $arr['tracker_name'] . '::'. $arr['field_name'];
				$id_arr[]=$arr['extra_field_id'];
		}
		echo '</strong></center></td>';
		echo '<td valign="top">';

		echo html_build_select_box_from_arrays($id_arr,$name_arr,'selectid',$selectid,false);
		echo '</td></tr>';
		echo '<tr><td>';
		?>
		<br />
	 	<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" />
		</td></tr></table></form>
		
		<?php
		$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
