<?php
//
//	FORM TO UPDATE ARTIFACT TYPES
//
		$ath->adminHeader(array ('title'=>_('Customize Browse List'),'pagename'=>'tracker_admin_customize_liste','titlevals'=>array($ath->getName())));

		/*
			List of possible user built Selection Boxes for an ArtifactType
		*/
		$efarr =& $ath->getExtraFields();
		
		$browse_fields = explode(',',$ath->getBrowseList());
		?>

		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="customize_list" value="y" />
		<p>
		<?php echo _('Set order of the fields that will be displayed on the browse view of your tracker:') ?>
		</p>
		
		<?php
		// Display regular fields.
		$fields = array (
			'summary' => _('Summary'),
			'open_date' => _('Open Date'),
			'status_id' => _('State'),
			'priority'  => _('Priority'),
			'assigned_to' => _('Assigned To'),
			'submitted_by' => _('Submitted By'),
			'close_date' => _('Close Date'),
			'details' => _('Detailed description')
			);

    if(count($ath->getExtraFields(ARTIFACT_EXTRAFIELDTYPE_STATUS)) > 0) {
      unset($fields['status_id']);
    }

		foreach ($fields as $f => $name) {
			$pos = array_search($f, $browse_fields);
			echo "<input type=\"text\" name=\"browse_fields[$f]\" value=\"" .
				 (($pos !== false) ? $pos + 1 : '') .
				 "\" size=\"3\" maxlength=\"3\" /> " .
				 $name .
				 "<br />\n";
		}	
		
		$keys=array_keys($efarr);
		$rows=count($keys);
		if ($rows > 0) {
			for ($k=0; $k < $rows; $k++) {
				$i=$keys[$k];
				$pos = array_search($i, $browse_fields);
				echo "<input type=\"text\" name=\"browse_fields[$i]\" value=\"" .
				 	 (($pos !== false) ? $pos + 1 : '') .
				 	 "\" size=\"3\" maxlength=\"3\" /> " .
					 $efarr[$i]['field_name'] .
					 "<br />\n";
			}
		}
		?>

		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
		</form>
		<?php

		$ath->footer(array());

?>
