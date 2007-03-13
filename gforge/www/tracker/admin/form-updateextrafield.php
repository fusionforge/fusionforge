<?php
//
//  FORM TO UPDATE POP-UP BOXES
//
		/*
			Allow modification of a artifact Selection Box
		*/
		$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_build_boxes','box_update_title',$ath->getName())));


		echo '
			<h2>'.$Language->getText('tracker_admin_build_boxes','box_update_title',$ath->getName()).'</h2>';

		$id = getStringFromRequest('id');
		$ac = new ArtifactExtraField($ath,$id);
		if (!$ac || !is_object($ac)) {
			$feedback .= 'Unable to create ArtifactExtraField Object';
		} elseif ($ac->isError()) {
			$feedback .= $ac->getErrorMessage();
		} else {
			?>
			<p>
			<strong><?php echo _('Type of custom field').': '.$ac->getTypeName(); ?></strong><br />
			
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&id='.$id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_box" value="y" />
			<input type="hidden" name="id" value="<?php echo $ac->getID(); ?>" />
			<input type="hidden" name="is_required" value="0" />
			<p>
			<strong><?php echo _('Custom Field Name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ac->getName(); ?>" /></p>
		<p>
		<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA || $ac->getType() == ARTIFACT_EXTRAFIELDTYPE_TEXT) {?>
		<?php echo _('Text Fields and Text Areas need to have Size/Maxlength and Rows/Cols defined, respectively.'); ?><br />
		<?php echo _('Text Field Size/Text Area Rows'); ?> <input type="text" name="attribute1" value="<?php echo $ac->getAttribute1(); ?>" size="2" maxlength="2"><br />
		<?php echo _('Text Field Maxlength/Text Area Columns'); ?> <input type="text" name="attribute2" value="<?php echo $ac->getAttribute2(); ?>" size="2" maxlength="2">
		<?php echo _('Text Fields and Text Areas need to have Size/Maxlength and Rows/Cols defined, respectively.'); ?><br />
		<?php } else { ?>
			<input type="hidden" name="attribute1" value="0" />
			<input type="hidden" name="attribute2" value="0" />
		<?php } ?>
			<p>
			<strong><?php echo _('Field alias') ?>:</strong><br />
			<input type="text" name="alias" value="<?php echo $ac->getAlias(); ?>" /></p>
			</p>
			<p>
			<span class="warning"><?php echo _('It is not recommended that you change the custom field name because other things are dependent upon it. When you change the custom field name, all related items will be changed to the new name') ?>
				</span></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
			</form></p>
			<?php
		}

		$ath->footer(array());

?>
