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
			$title_arr[]=_('Custom Fields Defined');
			$title_arr[]=_('Type');	
			$title_arr[]=_('Elements Defined');	
			$title_arr[]=_('Add Options');	
			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($k=0; $k < $rows; $k++) {
				$i=$keys[$k];
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.$efarr[$i]['field_name'].'<a href="'.getStringFromServer('PHP_SELF').'?update_box=1&amp;id='.
						$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						' ['._('Edit').']</a>'.
					'<a href="'.getStringFromServer('PHP_SELF').'?deleteextrafield=1&amp;id='.
                        $efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
                        ' ['._('Delete').']</a>'.
					'<a href="'.getStringFromServer('PHP_SELF').'?copy_opt=1&amp;id='.
                        $efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
                        ' ['._('Copy').']</a>'.
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
				
						echo '<a href="'.getStringFromServer('PHP_SELF').'?update_opt=1&amp;id='.
						$elearray[$j]['element_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'&amp;boxid='.
						$efarr[$i]['extra_field_id'].'">'.
						$elearray[$j]['element_name'].' ['._('Edit').']</a><br \>';

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
					echo '<a href="'.getStringFromServer('PHP_SELF').'?add_opt=1&amp;boxid='.
						$efarr[$i]['extra_field_id'].'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">['.
						_('add choices').']</a>';
				}
				echo '</td>'; 
			}
			echo   '</tr>';
			echo $GLOBALS['HTML']->listTableBottom();

		} else { 
			echo "\n<h3>"._('You have not defined any custom fields')."</h3>";
		}

		echo "<h2>"._('Add New Custom Field')."</h2>";
		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_extrafield" value="y" />
		<strong><?php echo _('Custom Field Name') ?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><?php echo _('Field alias') ?>:</strong><br />
		<input type="text" name="alias" value="" size="15" maxlength="30" /><br />
		<p>

		<strong><?php  echo _('Type of custom field') ?>:</strong><br />
		<input type="radio" name="field_type" value="1"> <?php echo _('Select Box'); ?><br />
		<input type="radio" name="field_type" value="2"> <?php echo _('Check Box'); ?><br />
		<input type="radio" name="field_type" value="3"> <?php echo _('Radio Buttons'); ?><br />
		<input type="radio" name="field_type" value="4"> <?php echo _('Text Field'); ?><br />
		<input type="radio" name="field_type" value="5"> <?php echo _('Multi-Select Box'); ?><br />
		<input type="radio" name="field_type" value="6"> <?php echo _('Text Area'); ?><br />
		<?php if (!$ath->usesCustomStatuses()) { ?>
		<input type="radio" name="field_type" value="7"> <?php echo _('Status'); ?><br />
		<?php } ?>
		<!--<input type="radio" name="field_type" value="8"> <?php echo _('MISSINGTEXT:tracker_admin_build_boxes/box_type_technician:TEXTMISSING'); ?><br />-->
		<p>
		<?php echo _('Text Fields and Text Areas need to have Size/Maxlength and Rows/Cols defined, respectively.'); ?><br />
		<?php echo _('Text Field Size/Text Area Rows'); ?>
			<input type="text" name="attribute1" value="0" size="2" maxlength="2"><br />
		<?php echo _('Text Field Maxlength/Text Area Columns'); ?>
			<input type="text" name="attribute2" value="0" size="2" maxlength="2">
		<p>
		<span class="warning"><?php echo _('MISSINGTEXT:tracker_admin_build_boxes/box_warning:TEXTMISSING') ?></span></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
		</form></p>
		<?php

		echo "<h2>"._('Custom Field Rendering Template')."</h2><p>";

		echo '<a href="'.getStringFromServer('PHP_SELF').'?downloadtemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'._('Download default template').'</a><br />';
		echo '<a href="'.getStringFromServer('PHP_SELF').'?uploadtemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'._('Add/Update template').'</a><br />';
		echo '<a href="'.getStringFromServer('PHP_SELF').'?deletetemplate=1&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'._('Delete template').'</a><br />';

		$ath->footer(array());

?>
