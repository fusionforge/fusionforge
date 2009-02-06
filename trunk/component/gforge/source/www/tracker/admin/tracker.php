<?php
//
//  SHOW LINKS TO FUNCTIONS
//

		$ath->adminHeader(array ('title'=>_('Administration').': '.$ath->getName()));
//
//	Reference to build a selection box for a tracker like bugs, etc
//
		echo '<p>
			<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_extrafield=1"><strong>'._('Manage Custom Fields').'</strong></a><br />
			'._('Add new boxes like Phases, Quality Metrics, Components, etc.  Once added they can be used with other selection boxes (for example, Categories or Groups) to describe and browse bugs or other artifact types').'</p>';
		echo '<p>
			<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;add_canned=1"><strong>'._('Add/Update Canned Responses').'</strong></a><br />
			'._('Create/Change generic response messages for the tracker').'</p>';
		echo '<p>
			<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;delete=1"><strong>'._('Delete').'</strong></a><br />
			'._('Permanently delete this tracker.').'</p>';
		echo '<p>
			<a href="'.getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;update_type=1"><strong>'._('Update preferences').'</strong></a><br />
			'._('Set up prefs like expiration times, email addresses').'.</p>';

		$ath->footer(array());

?>
