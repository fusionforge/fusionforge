<?php
//
//	FORM TO UPDATE ARTIFACT TYPES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_update_type','title', $ath->getName())));

		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="update_type" value="y" />
		<p>
		<?php echo _('<strong> Name:</strong> (examples: meeting minutes, test results, RFP Docs)') ?><br />
		<?php if ($ath->getDataType()) {
			echo $ath->getName();
		} else { 
			?>
			<input type="text" name="name" value="<?php echo $ath->getName(); ?>" /></p>
			<?php 
		} 
		?>
		<p>
		<strong><?php echo _('Description') ?>:</strong><br />
		<?php if ($ath->getDataType()) {
			echo $ath->getDescription();
		} else {
			?>
			<input type="text" name="description" value="<?php echo $ath->getDescription(); ?>" size="50" /></p>
			<?php 
		} 
		?>
		<p>
		<strong><?php echo _('Send email on new submission to address') ?>:</strong><br />
		<input type="text" name="email_address" value="<?php echo $ath->getEmailAddress(); ?>" /></p>
		<p>
		<input type="checkbox" name="email_all" value="1" <?php echo (($ath->emailAll())?'checked="checked"':''); ?> /> <strong><?php echo _('Send email on all changes') ?></strong><br /></p>
		<p>
		<strong><?php echo _('Days till considered overdue') ?>:</strong><br />
		<input type="text" name="due_period" value="<?php echo ($ath->getDuePeriod() / 86400); ?>" /></p>
		<p> 
		<strong><?php echo _('Days till pending tracker items time out') ?>:</strong><br />
		<input type="text" name="status_timeout"  value="<?php echo($ath->getStatusTimeout() / 86400); ?>" /></p>
		<p>
		<strong><?php echo _('Free form text for the "submit new item" page') ?>:</strong><br />
		<textarea name="submit_instructions" rows="10" cols="55" wrap="hard"><?php echo $ath->getSubmitInstructions(); ?></textarea></p>
		<p>
		<strong><?php echo _('Free form text for the "browse items" page') ?>:</strong><br />
		<textarea name="browse_instructions" rows="10" cols="55" wrap="hard"><?php echo $ath->getBrowseInstructions(); ?></textarea></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

?>
