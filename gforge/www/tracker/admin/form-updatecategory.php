<?php
//
//  FORM TO UPDATE CATEGORIES
//
		/*
			Allow modification of a artifact category
		*/
		$ath->adminHeader(array('title'=>$Language->getText('tracker_admin_update_cat','title',$ath->getName())));

		echo '
			<h1>'.$Language->getText('tracker_admin_update_cat','title',$ath->getName()).'</h1>';

		$ac = new ArtifactCategory($ath,$id);
		if (!$ac || !is_object($ac)) {
			$feedback .= 'Unable to create ArtifactCategory Object';
		} elseif ($ac->isError()) {
			$feedback .= $ac->getErrorMessage();
		} else {
			?>
			<p>
			<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
			<input type="hidden" name="update_cat" value="y" />
			<input type="hidden" name="id" value="<?php echo $ac->getID(); ?>" />
			<p>
			<strong><?php echo $Language->getText('tracker_admin_update_cat','category_name') ?>:</strong><br />
			<input type="text" name="name" value="<?php echo $ac->getName(); ?>" /></p>
			<p>
			<strong><?php echo $Language->getText('tracker_admin_update_cat','auto_assign_to') ?>:</strong><br />
			<?php echo $ath->technicianBox('assign_to',$ac->getAssignee()); ?></p>
			<p>
			<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_update_cat','category_change_warning') ?>
				</span></strong></p>
			<p>
			<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
			</form></p>
			<?php
		}

		$ath->footer(array());

?>
