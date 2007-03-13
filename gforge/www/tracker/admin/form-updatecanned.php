<?php
//
//	FORM TO UPDATE CANNED MESSAGES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_update_canned','title', $ath->getName())));

		echo "<h1>".$Language->getText('tracker_admin_update_canned','title', $ath->getName())."</h1>";

		$id = getStringFromRequest('id');
		$acr = new ArtifactCanned($ath,$id);
		if (!$acr || !is_object($acr)) {
			$feedback .= 'Unable to create ArtifactCanned Object';
		} elseif ($acr->isError()) {
			$feedback .= $acr->getErrorMessage();
		} else {
			?>
			<p><?php echo _('Creating useful generic messages can save you a lot of time when handling common artifact requests.') ?></p>
			<p>
			<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_canned" value="y" />
			<input type="hidden" name="id" value="<?php echo $acr->getID(); ?>" />
			<strong><?php echo _('Title') ?>:</strong><br />
			<input type="text" name="title" value="<?php echo $acr->getTitle(); ?>" size="50" maxlength="50" />
			<p>
			<strong><?php echo _('Message Body') ?>:</strong><br />
			<textarea name="body" rows="30" cols="65" wrap="hard"><?php echo $acr->getBody(); ?></textarea></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
			</form></p>
			<?php
		}
		$ath->footer(array());

?>
