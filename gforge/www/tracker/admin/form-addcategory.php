<?php

//
//  FORM TO ADD CATEGORIES
//
		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin_add_cat','title',$ath->getName())));

		echo "<h1>".$Language->getText('tracker_admin_add_cat','title',$ath->getName())."</h1>";

		/*
			List of possible categories for this ArtifactType
		*/
		$result=$ath->getCategories();
		echo "<p>&nbsp;</p>";
		$rows=db_numrows($result);
		if ($result && $rows > 0) {
			$title_arr=array();
			$title_arr[]=$Language->getText('tracker_admin','tracker_id');
			$title_arr[]=$Language->getText('tracker_admin','tracker_title');
			
			echo $GLOBALS['HTML']->listTableTop ($title_arr);
			
			for ($i=0; $i < $rows; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>'.
					'<td>'.db_result($result, $i, 'id').'</td>'.
					'<td><a href="'.$PHP_SELF.'?update_cat=1&amp;id='.
						db_result($result, $i, 'id').'&amp;group_id='.$group_id.'&amp;atid='. $ath->getID() .'">'.
						db_result($result, $i, 'category_name').'</a></td></tr>';
			}		   

			echo $GLOBALS['HTML']->listTableBottom();

		} else {
			echo "\n<h1>".$Language->getText('tracker_admin_add_cat','no_categories')."</h1>";
		}
		?>
		<p>
		<form action="<?php echo $PHP_SELF.'?group_id='.$group_id.'&atid='.$ath->getID(); ?>" method="post">
		<input type="hidden" name="add_cat" value="y" />
		<strong><?php echo $Language->getText('tracker_admin','category_name') ?>:</strong><br />
		<input type="text" name="name" value="" size="15" maxlength="30" /><br />
		<p>
		<strong><?php echo $Language->getText('tracker_admin','auto_assign_to') ?>:</strong><br />
		<?php echo $ath->technicianBox('assign_to'); ?></p>
		<p>
		<strong><span style="color:red"><?php echo $Language->getText('tracker_admin','category_add_warning') ?></span></strong></p>
		<p>
		<input type="submit" name="post_changes" value="<?php echo$Language->getText('general','submit') ?>" /></p>
		</form></p>
		<?php

		$ath->footer(array());

?>
