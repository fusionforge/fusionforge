<?php
//
//  FORM TO BUILD SELECTION BOXES 
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_build_boxes','title',$ath->getName())));

		echo "<h2>".$Language->getText('tracker_admin_build_boxes','title',$ath->getName())."</h2>";

		/*
			List of possible user built Selection Boxes for an ArtifactType
		*/
		$efarr =& $ath->getExtraFields();
		$eftypes=ArtifactExtraField::getAvailableTypes();
		$keys=array_keys($efarr);
		echo "<br />";
		$rows=count($keys);
		if ($rows > 0) {

			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin_build_boxes','tracker_box_title');
			$title_arr[]=$Language->getText('tracker_admin_build_boxes','tracker_box_type');	
			$title_arr[]=$Language->getText('tracker_admin_build_boxes','tracker_box_option_title');	
			$title_arr[]=$Language->getText('tracker_admin_build_boxes','tracker_box_add_options');	
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($k=0; $k < $rows; $k++) {
				$i=$keys[$k];
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.$efarr[$i]['field_name'].'<a href="'.$PHP_SELF.'?update_box=1&amp;id='.
						$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						' ['.$Language->getText('tracker_admin_build_boxes','edit').']</a>'.
					'<a href="'.$PHP_SELF.'?deleteextrafield=1&amp;id='.
                        $efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
                        ' ['.$Language->getText('tracker_admin_build_boxes','delete').']</a>'.
					'<a href="'.$PHP_SELF.'?copy_opt=1&amp;id='.
                        $efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
                        ' ['.$Language->getText('tracker_admin_build_boxes','copy').']</a>'.
					'</td>';
				echo '<td>'.$eftypes[$efarr[$i]['field_type']].'</td>';
				/*
		  			List of possible options for a user built Selection Box
		  		*/
				$elearray = $ath->getExtraFieldElements($efarr[$i]['extra_field_id']);	
			
				if (!empty($elearray)) {
					$optrows=count($elearray);

					echo '<td>';
					for ($j=0; $j <$optrows; $j++)
				
						echo '<a href="'.$PHP_SELF.'?update_opt=1&amp;id='.
						$elearray[$j]['element_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;boxid='.
						$efarr[$i]['extra_field_id'].'">'.
						$elearray[$j]['element_name'].' ['.$Language->getText('tracker_admin_build_boxes','edit').']</a><br \>';

					} else {
						echo '<td>';
				}
				
				echo '</td>';
				echo '<td>';
				if ($efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_SELECT
					|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_RADIO 
					|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_CHECKBOX
					|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_MULTISELECT 
					|| $efarr[$i]['field_type'] == ARTIFACT_EXTRAFIELDTYPE_STATUS) {
					echo '<a href="'.$PHP_SELF.'?add_opt=1&amp;boxid='.
						$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">['.
						$Language->getText('tracker_admin_build_boxes', 'box_add_choices').']</a>';
				}
				echo '</td>'; 
			}
			echo   '</tr>';
			echo $GLOBALS['HTML']->listTableBottom();

		} else { 
			echo "\n<h3>".$Language->getText('tracker_admin_build_boxes','no_box')."</h3>";
		}

		echo "<h2>".$Language->getText('tracker_admin_build_boxes','add_field')."</h2>";
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_extrafield" value="y" />
		<strong><?php echo $Language->getText('tracker_admin_build_boxes','box_name') ?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><?php  echo $Language->getText('tracker_admin_build_boxes','box_type') ?>:</strong><br />
		<input type="radio" name="field_type" value="1"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_select'); ?><br />
		<input type="radio" name="field_type" value="2"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_checkbox'); ?><br />
		<input type="radio" name="field_type" value="3"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_radio'); ?><br />
		<input type="radio" name="field_type" value="4"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_text'); ?><br />
		<input type="radio" name="field_type" value="5"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_multiselect'); ?><br />
		<input type="radio" name="field_type" value="6"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_textarea'); ?><br />
		<?php if (!$ath->usesCustomStatuses()) { ?>
		<input type="radio" name="field_type" value="7"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_status'); ?><br />
		<?php } ?>
		<!--<input type="radio" name="field_type" value="8"> <?php echo $Language->getText('tracker_admin_build_boxes','box_type_technician'); ?><br />-->
		<p>
		<?php echo $Language->getText('tracker_admin_build_boxes','box_sizerows'); ?><br />
		<?php echo $Language->getText('tracker_admin_build_boxes','box_sizeattr1'); ?>
			<input type="text" name="attribute1" value="0" size="2" maxlength="2"><br />
		<?php echo $Language->getText('tracker_admin_build_boxes','box_sizeattr2'); ?>
			<input type="text" name="attribute2" value="0" size="2" maxlength="2">
		<p>
		<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_build_boxes','box_warning') ?></span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		echo "<h2>".$Language->getText('tracker_admin_build_boxes','manage_template')."</h2><p>";

		echo '<a href="'.$PHP_SELF.'?downloadtemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.$Language->getText('tracker_admin_build_boxes','download_template').'</a><br />';
		echo '<a href="'.$PHP_SELF.'?uploadtemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.$Language->getText('tracker_admin_build_boxes','upload_template').'</a><br />';
		echo '<a href="'.$PHP_SELF.'?deletetemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.$Language->getText('tracker_admin_build_boxes','delete_template').'</a><br />';

		$ath->footer(array());

?>
