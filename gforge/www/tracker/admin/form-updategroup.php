<?php
//
//  FORM TO UPDATE GROUPS
//
		/*
			Allow modification of a artifact group
		*/
		$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_update_group','title',$ath->getName()),'pagename'=>'tracker_admin_update_group','titlevals'=>array($ath->getName())));

		$ag = new ArtifactGroup($ath,$id);
		if (!$ag || !is_object($ag)) {
			$feedback .= 'Unable to create ArtifactGroup Object';
		} elseif ($ag->isError()) {
			$feedback .= $ag->getErrorMessage();
		} else {
			?>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_group" value="y" />
			<input type="hidden" name="id" value="<?php echo $ag->getID(); ?>" />
			<p>
			<strong><?php echo $Language->getText('tracker_admin','group_name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ag->getName(); ?>" /></p>
			<p>
			<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_update_group','warning') ?></span></strong></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
		}

		$ath->footer(array());

?>
