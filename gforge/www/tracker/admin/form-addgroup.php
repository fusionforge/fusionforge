<?php
//
//  FORM TO ADD GROUP
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_add_group','title', $ath->getName()),'pagename'=>'tracker_admin_add_group','titlevals'=>array($ath->getName())));

		/*
			List of possible groups for this ArtifactType
		*/
		$result=$ath->getGroups();
		echo "<p>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin','tracker_id');
			$title_arr[]=$Language->getText('tracker_admin','tracker_title');
			
			echo $GLOBALS['HTML']->listTableTop ($title_arr);
			
			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.db_result($result, $i, 'id').'</td>'.
					'<td><a href="'.$PHP_SELF.'?update_group=1&amp;id='.
						db_result($result, $i, 'id').'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						db_result($result, $i, 'group_name').'</a></td></tr>';
			}		   

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo "\n<h1>".$Language->getText('tracker_admin_add_group','no_groups_defined')."</h1>";
		}
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_group" value="y" />
		<strong><?php echo $Language->getText('tracker_admin','group_name')?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><span style="color:red"><?php echo $Language->getText('tracker_admin_add_group','group_add_warning') ?></span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

?>
