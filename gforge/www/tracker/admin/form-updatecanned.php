<?php
//
//	FORM TO UPDATE CANNED MESSAGES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_update_canned','title', $ath->getName())));

		echo "<h1>".$Language->getText('tracker_admin_update_canned','title', $ath->getName())."</h1>";

		$acr = new ArtifactCanned($ath,$id);
		if (!$acr || !is_object($acr)) {
			$feedback .= 'Unable to create ArtifactCanned Object';
		} elseif ($acr->isError()) {
			$feedback .= $acr->getErrorMessage();
		} else {
			?>
			<p><?php echo $Language->getText('tracker_admin_add_canned','canned_response_info') ?></p>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_canned" value="y" />
			<input type="hidden" name="id" value="<?php echo $acr->getID(); ?>" />
			<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_title') ?>:</strong><br />
			<input type="text" name="title" value="<?php echo $acr->getTitle(); ?>" size="50" maxlength="50" />
			<p>
			<strong><?php echo $Language->getText('tracker_admin_add_canned','canned_response_body') ?>:</strong><br />
			<textarea name="body" rows="30" cols="65" wrap="hard"><?php echo $acr->getBody(); ?></textarea></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
		}
		$ath->footer(array());

?>
