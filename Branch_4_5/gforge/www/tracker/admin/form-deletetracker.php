<?php

		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin','delete', $ath->getName())));

		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="delete" value="y" /><br />
		<?php echo $Language->getText('tracker_admin','delete_warning'); ?>
		<p>
		<input type="checkbox" name="sure" value="1"><?php echo $Language->getText('tracker_admin','sure') ?><br />
		<input type="checkbox" name="really_sure" value="1"><?php echo $Language->getText('tracker_admin','really_sure') ?><br />
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('tracker_admin','delete') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

?>
