<?php

//
//  FORM TO COPY Choices configured by admin for extra_field BOXES 
//
		$fb= new ArtifactExtraField($ath,$boxid);
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_copy','choices_title',$fb->getName())));
		echo "<h3>".$Language->getText('tracker_admin_copy','choices_title',$fb->getName())."</h3>";
		
		$efearr =& $ath->getExtraFieldElements($boxid);
		for ($i=0; $i<count($efearr); $i++) {
			$field_id_arr[] = $efearr[$i]['element_id'];
			$field_arr[] = $efearr[$i]['element_name'];
		}
		echo '<table>';
		echo '<tr>';
		echo '<td></td><td><center><strong>';
		echo $Language->getText('tracker_admin_copy','from_box');
		echo '<br />';
		echo $fb->getName();
		echo '</center></strong></td><td></td><td><strong><center>';
		
		echo $Language->getText('tracker_admin_copy','into_box');
		echo '</center></strong></tr><tr><td><strong><center>';
		echo '</center></strong></td>';
		echo '<td valign=top>';
		?>
		
		<form action="<?php echo $PHP_SELF .'?group_id='.$group_id.'&boxid='.$boxid.'&atid='.$ath->getID(); ?>" method="post" >
		<input type="hidden" name="copy_opt" value="copy" >
		<input type="hidden" value="$return">
		<?php
		echo html_build_multiple_select_box_from_arrays($field_id_arr,$field_arr,'copyid[]',array(),10,false);
		echo '</td><td><strong><center>';
		$atf = new ArtifactTypeFactory($group);
		$at_arr =& $atf->getArtifactTypes();
		for ($j=0; $j < count($at_arr); $j++) {
			$athcp= new ArtifactTypeHtml($group,$at_arr[$j]->getID());
			$efarr =& $athcp->getExtraFields();
			$ct=count($efarr);
			for ($k=0; $k < $ct; $k++) {
				$id_arr[]=$efarr[$k]['extra_field_id'];
				$name_arr[]=$athcp->getName() .' - '.$efarr[$k]['field_name'];
			}
			unset ($athcp);	
		}
		echo '<td valign=top>';

		$cat_count=count($id_arr);
		echo html_build_multiple_select_box_from_arrays($id_arr,$name_arr,'selectid[]',array(),10,false);
		echo '</td></tr>';
		echo '<tr><td>';
		?>
		<br />
	 	<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" />
		</td></tr></table></form>
		
		<?php
		$ath->footer(array());

?>
