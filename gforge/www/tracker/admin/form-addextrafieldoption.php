<?php

//
//  FORM TO ADD ELEMENTS TO EXTRA FIELD
//
	$ac = new ArtifactExtraField($ath,$boxid);
	if (!$ac || !is_object($ac)) {
		exit_error('Error','Unable to create ArtifactExtraField Object');
	} elseif ($ac->isError()) {
		exit_error('Error',$ac->getErrorMessage());
	} else {
		$efearr=$ath->getExtraFieldElements($boxid);
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_build_boxes','title')));

		echo "<h3>".$Language->getText('tracker_admin_build_boxes','opt_title',$ath->getName())."</h3>";
		echo "<br />";
		$rows=count($efearr);
		if ($rows > 0) {
			
			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin_build_boxes','tracker_box_option_title');

			echo $GLOBALS['HTML']->listTableTop ($title_arr);

			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td><a href="'.$PHP_SELF.'?update_opt=1&amp;id='.
					$efearr[$i]['element_id'].'&amp;boxid='.			
					$boxid.'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
					$efearr[$i]['element_name'].' ['.$Language->getText('tracker_admin_build_boxes','edit').']</a></td>';
			}		   
			echo $GLOBALS['HTML']->listTableBottom();

		} else { 
			echo "\n<h3>".$Language->getText('tracker_admin_build_boxes','no_choice')."</h3>";
		}
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&boxid='.$boxid.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_opt" value="y" />
		<strong><?php echo $Language->getText('tracker_admin_build_boxes','opt_add_name') ?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /> <br \>
		<!--
		Show a pop-up box to choose the possible statuses that this element will map to
		-->
		<?php if ($ac->getType() == ARTIFACT_EXTRAFIELDTYPE_STATUS) { ?>
		<strong><?php echo $Language->getText('tracker_admin_build_boxes','box_status'); ?>:</strong><br />
		<?php echo $ath->statusBox('status_id',1,false,false); ?>
		<?php } ?>
		<p>
		<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_build_boxes','choice_warning') ?></span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo$Language->getText('general','submit') ?>" /></p>
		</form>
		</p>
		<?php
		$ath->footer(array());
	}
?>
