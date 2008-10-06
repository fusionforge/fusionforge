<?php
//
//  SHOW LINKS TO FUNCTIONS
//

		$ath->adminHeader(array ('title'=>$Language->getText('tracker_admin','title').': '.$ath->getName(),'pagename'=>'tracker_admin','titlevals'=>array($ath->getName())));
//
//	Reference to build a selection box for a tracker like bugs, etc
//
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_extrafield=1"><strong>'.$Language->getText('tracker_admin','build_selection_box').'</strong></a><br />
			'.$Language->getText('tracker_admin','build_selection_box_info').'</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_canned=1"><strong>'.$Language->getText('tracker_admin','add_canned_responses').'</strong></a><br />
			'.$Language->getText('tracker_admin','add_canned_responses_info').'</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;delete=1"><strong>'.$Language->getText('tracker_admin','delete').'</strong></a><br />
			'.$Language->getText('tracker_admin','permanently_delete_info').'</p>';
		echo '<p>
			<a href="'.$PHP_SELF.'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_type=1"><strong>'.$Language->getText('tracker_admin','update_preferences').'</strong></a><br />
			'.$Language->getText('tracker_admin','update_preferences_info').'.</p>';

		$ath->footer(array());

?>
