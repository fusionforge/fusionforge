<?php
//
//  FORM TO UPDATE POP-UP CHOICES FOR A BOX
//
		/*
			Allow modification of a Choice for a Pop-up Box
		*/
		$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_build_boxes','opt_update_title',$ath->getName())));

		echo '
			<h2>'.$Language->getText('tracker_admin_build_boxes','opt_update_title',$ath->getName()).'</h2>';

		$ao = new ArtifactExtraFieldElement($ath,$id);
		if (!$ao || !is_object($ao)) {
			$feedback .= 'Unable to create ArtifactExtraFieldElement Object';
		} elseif ($ao->isError()) {
			$feedback .= $ao->getErrorMessage();
		} else {

			?>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_opt" value="y" />
			<input type="hidden" name="id" value="<?php echo $ao->getID(); ?>" />

			<p>
			<strong><?php echo $Language->getText('tracker_admin_build_boxes','opt_name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ao->getName(); ?>" /></p>
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
