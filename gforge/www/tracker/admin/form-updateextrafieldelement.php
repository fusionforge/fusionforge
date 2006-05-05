<?php
//
//  FORM TO UPDATE POP-UP CHOICES FOR A BOX
//
	/*
		Allow modification of a Choice for a Pop-up Box
	*/
	$boxid = getIntFromRequest('boxid');
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		exit_error('Error','Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		exit_error('Error',$ac->getErrorMessage());
	} else {
		$id = getStringFromRequest('id');
		$ao = new ArtifactExtraFieldElement($ac,$id);
		if (!$ao || !is_object($ao)) {
			exit_error('Error','Unable to create ArtifactExtraFieldElement Object');
		} elseif ($ao->isError()) {
			exit_error('Error',$ao->getErrorMessage());
		} else {

			$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_build_boxes','opt_update_title',$ath->getName())));

			echo '
				<h2>'.$Language->getText('tracker_admin_build_boxes','opt_update_title',$ath->getName()).'</h2>';

			?>
			<p>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_opt" value="y" />
			<input type="hidden" name="id" value="<?php echo $ao->getID(); ?>" />
			<input type="hidden" name="boxid" value="<?php echo $boxid; ?>" />

			<p>
			<strong><?php echo $Language->getText('tracker_admin_build_boxes','opt_name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ao->getName(); ?>" /></p>
			<!--
			Show a pop-up box to choose the possible statuses that this element will map to
			-->
			<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) { ?>
			<strong><?php echo $Language->getText('tracker_admin_build_boxes','box_status'); ?>:</strong><br />
			<?php echo $ath->statusBox('status_id',$ao->getStatusID(),false,false); ?>
			<?php } ?>

			<p>
			<span class="warning"><?php echo $Language->getText('tracker_admin_build_boxes','box_change_warning') ?>
				</span></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
			$ath->footer(array());
		}
	}

?>
