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

		$ac = new ArtifactExtraField($ath,$id);
		if (!$ac || !is_object($ac)) {
			$feedback .= 'Unable to create ArtifactExtraField Object';
		} elseif ($ac->isError()) {
			$feedback .= $ac->getErrorMessage();
		} else {
			?>
			<p>
			<strong><?php echo $Language->getText('tracker_admin_build_boxes','box_type').': '.$ac->getTypeName(); ?></strong><br />
			
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&id='.$id.'&boxid='.$box.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_box" value="y" />
			<input type="hidden" name="id" value="<?php echo $ac->getID(); ?>" />
			<p>
			<strong><?php echo $Language->getText('tracker_admin_build_boxes','box_name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ac->getName(); ?>" /></p>
		<p>
		<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_TEXTAREA || $ac->getType() == ARTIFACT_EXTRAFIELDTYPE_TEXT) {?>
		<?php echo $Language->getText('tracker_admin_build_boxes','box_sizerows'); ?><br />
		<?php echo $Language->getText('tracker_admin_build_boxes','box_sizeattr1'); ?> <input type="text" name="attribute1" value="<?php echo $ac->getAttribute1(); ?>" size="2" maxlength="2"><br />
		<?php echo $Language->getText('tracker_admin_build_boxes','box_sizeattr2'); ?> <input type="text" name="attribute2" value="<?php echo $ac->getAttribute2(); ?>" size="2" maxlength="2">
		<?php echo $Language->getText('tracker_admin_build_boxes','box_sizerows'); ?><br />
		<?php } ?>
			<p>
			<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_build_boxes','box_change_warning') ?>
				</span></strong></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
		}

		$ath->footer(array());

?>
