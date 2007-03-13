<?php

		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin','delete', $ath->getName())));

		$id = getStringFromRequest('id');

		?>
		<p>
		<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="deleteextrafield" value="y" /><br />
		<input type="hidden" name="id" value="<?php echo $id; ?>" /><br />
		<?php echo _('You are about to permanently and irretrievably delete this custom field and all its contents!'); ?>
		<p>
		<input type="checkbox" name="sure" value="1"><?php echo _('I\'m Sure.') ?><br />
		<input type="checkbox" name="really_sure" value="1"><?php echo _('I\'m Really Sure.') ?><br />
		<p>
		<input type="submit" name="post_changes" value="<?php echo _('Delete') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

?>
