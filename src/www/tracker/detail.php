<?php
/**
 * Tracker Detail
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * http://fusionforge.org/
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

use_javascript('/tabber/tabber.js');

if (getStringFromRequest('commentsort') == 'anti') {
       $sort_comments_chronologically = false;
} else {
       $sort_comments_chronologically = true;
}

$ath->header(array ('title'=>'[#'. $ah->getID(). '] ' . $ah->getSummary(), 'atid'=>$ath->getID()));

echo notepad_func();

?>
	<form id="trackerdetailform" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;atid=<?php echo $ath->getID(); ?>" method="post" enctype="multipart/form-data">

<?php if (session_loggedin()) { ?>
	<table cellpadding="0" width="100%">
		<tr>
			<td>
				<?php 
					if ($ah->isMonitoring()) {
						$img="xmail16w.png";
						$key="monitorstop";
						$text=_('Stop monitor');
					} else {
						$img="mail16w.png";
						$key="monitor";
						$text=_('Monitor');
					}
					echo '
					<a id="tracker-monitor" href="index.php?group_id='.$group_id.'&amp;artifact_id='.$ah->getID().'&amp;atid='.$ath->getID().'&amp;func=monitor" title="'.html_get_tooltip_description('monitor').'"><strong>'.
						html_image('ic/'.$img.'','20','20').' '.$text.'</strong></a>';
					?>
			</td>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
	</table>
<?php } ?>
	<table border="0" width="80%">
		<tr>
			<td><strong><?php echo _('Date') ?>:</strong><br /><?php echo date( _('Y-m-d H:i'), $ah->getOpenDate() ); ?></td>
			<td><strong><?php echo _('Priority') ?>:</strong><br /><?php echo $ah->getPriority(); ?></td>
		</tr>

		<tr>
			<td><strong><?php echo _('State') ?>:</strong><br /><?php echo $ah->getStatusName(); ?></td>
			<td></td>
		</tr>
		<tr>
	        <td>
			<strong><?php echo _('Submitted by') ?>:</strong><br />
			<?php echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				$submittedBy = $ah->getSubmittedBy();
				?>
				(<tt><?php echo util_make_link_u ($submittedUnixName,$submittedBy,$submittedUnixName); ?></tt>)
			<?php } ?>
			</td>
			<td><strong><?php echo _('Assigned to') ?>:</strong><br />
			<?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</td>
		</tr>

		<?php
			$ath->renderExtraFields($ah->getExtraFieldData(),true,'none',false,'Any',array(),false,'DISPLAY');
			$ath->renderRelatedTasks($group, $ah);
		?>

		<tr><td colspan="2"><strong><?php echo _('Summary') ?>:</strong><br /><?php echo $ah->getSummary(); ?></td></tr>

		<tr><td colspan="2">
			<br />
			<?php echo $ah->showDetails(); ?>
		</td></tr>
</table>
<div id="tabber" class="tabber">
<div class="tabbertab" title="<?php echo _('Followups'); ?>">
	<table border="0" width="80%">
		<tr><td colspan="2">
			<?php if ($ath->allowsAnon() || session_loggedin()) { ?>
			<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
			<input type="hidden" name="func" value="postmod" />
			<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
			<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>" />
			<p>
			<strong><?php echo _('Add A Comment') ?>:</strong> 
			<?php echo notepad_button('document.forms.trackerdetailform.details') ?><br />
			<textarea name="details" rows="10" cols="60"></textarea>
			</p>
			<?php } ?>
		</td></tr>
		<tr><td colspan="2">
		<h2><?php echo _('Followups: ') 
		if ($sort_comments_chronologically) {
			echo '<a href="' .
			util_make_url('/tracker/index.php?func=detail&amp;aid=' . $aid . '&amp;group_id=' . $group_id . '&amp;atid=' . $ath->getID() . '&amp;commentsort=anti') .
			'">' . _('Sort comments antichronologically') . '</a>';
		} else {
			echo '<a href="' .
				util_make_url('/tracker/index.php?func=detail&amp;aid=' . $aid . '&amp;group_id=' . $group_id . '&amp;atid=' . $ath->getID() . '&amp;commentsort=chrono') .
				'">' . _('Sort comments chronologically') . '</a>';
		}
		echo '</h2>';

		echo $ah->showMessages($sort_comments_chronologically);
		?>
		</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Attachments'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
	<?php if (session_loggedin() && ($ah->getSubmittedBy() == user_getid())) { ?>
		<strong><?php echo _('Attach Files'); ?></strong><br />
		<input type="file" name="input_file0" size="30" /><br />
		<input type="file" name="input_file1" size="30" /><br />
		<input type="file" name="input_file2" size="30" /><br />
		<input type="file" name="input_file3" size="30" /><br />
		<input type="file" name="input_file4" size="30" /><br />
		<p />
	<?php } ?>
	<h2><?php echo _('Attached Files') ?>:</h2>
	<?php
	//
	//  print a list of files attached to this Artifact
	//
		$ath->renderFiles($group_id, $ah);
	?>
	</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Commits'); ?>" >
<table border="0" width="80%">
	<?php
		$hookParams['artifact_id']=$aid;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Changes'); ?>">
<table border="0" width="80%">
	<tr>
	<td colspan="2">
	<h2><?php echo _('Changes') ?>:</h2>
	<?php
	echo $ah->showHistory();
	?>
	</td>
	</tr>
</table>
</div>
<?php $ah->showRelations(); ?>
</div>
</form>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
