<?php
/**
 * Tracker Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2014, Franck Villaume - TrivialDev
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * http://fusionforge.org/
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

use_javascript('/tabber/tabber.js');

global $ath;
global $ah;
global $group_id;
global $group;
global $aid;
global $atid;

html_use_coolfieldset();
$ath->header(array ('title'=> $ah->getStringID().' '. $ah->getSummary(), 'atid'=>$ath->getID()));

echo notepad_func();

?>
	<h1>[#<?php echo $ah->getID(); ?>] <?php echo $ah->getSummary(); ?></h1>

<form id="trackermodlimitedform" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;atid=<?php echo $ath->getID(); ?>" enctype="multipart/form-data" method="post">
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
<input type="hidden" name="func" value="postmod" />
<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>" />

<table width="80%">
<?php
if (session_loggedin()) {
?>
		<tr>
			<td><?php
				if ($ah->isMonitoring()) {
					$img="xmail16w.png";
					$key="monitorstop";
					$text=_('Stop Monitoring');
				} else {
					$img="mail16w.png";
					$key="monitor";
					$text=_('Monitor');
				}
				echo '
				<a id="tracker-monitor" href="index.php?group_id='.$group_id.'&amp;artifact_id='.$ah->getID().'&amp;atid='.$ath->getID().'&amp;func=monitor" title="'.util_html_secure(html_get_tooltip_description('monitor')).'"><strong>'.
					html_image('ic/'.$img.'','20','20').' '.$text.'</strong></a>';
				?>
			</td>
			<td><?php
					$votes = $ah->getVotes();
					echo '<span id="tracker-votes" title="'.html_get_tooltip_description('votes').'" >'.html_e('strong', array(), _('Votes') . _(': ')).sprintf('%1$d/%2$d (%3$d%%)', $votes[0], $votes[1], $votes[2]).'</span>';
					if ($ath->canVote()) {
						if ($ah->hasVote()) {
							$key = 'pointer_down';
							$txt = _('Retract Vote');
						} else {
							$key = 'pointer_up';
							$txt = _('Cast Vote');
						}
						echo '<a id="tracker-vote" alt="'.$txt.'" title="'.html_get_tooltip_description('vote').'" href="'.getselfhref(array('func' => $key)) . '">' .
							html_image('ic/' . $key . '.png', '16', '16', array('border' => '0')) . '</a>';
					}
					?>
			</td>
			<td><?php
				if ($group->usesPM()) {
					echo '
				<a href="'.getStringFromServer('PHP_SELF').'?func=taskmgr&amp;group_id='.$group_id.'&amp;atid='.$atid.'&amp;aid='.$aid.'">'.
					html_image('ic/taskman20w.png','20','20').'<strong>'._('Build Task Relation').'</strong></a>';
				}
				?>
			</td>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
</table>
<br />
<?php } ?>
<table width="80%">
	<tr>
		<td><strong><?php echo _('Submitted by')._(':'); ?></strong><br />
			<?php
			echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				$submittedBy = $ah->getSubmittedBy();
				?>
				(<tt><?php echo util_make_link_u ($submittedUnixName,$submittedBy,$submittedUnixName); ?></tt>)
			<?php } ?>
		</td>
		<td><strong><?php echo _('Date Submitted')._(':'); ?></strong><br />
		<?php
		echo date(_('Y-m-d H:i'), $ah->getOpenDate() );

		$close_date = $ah->getCloseDate();
		if ($ah->getStatusID()==2 && $close_date > 1) {
			echo '<br /><strong>'._('Date Closed')._(':').'</strong><br />'
				 .date(_('Y-m-d H:i'), $close_date);
		}
		?>
		</td>
	</tr>

	<?php
		$ath->renderExtraFields($ah->getExtraFieldData(),true,'none',false,'Any',array(),false,'UPDATE');
	?>

	<tr>
		<td><strong><?php echo _('Assigned to')._(':'); ?></strong><br />
		<span id="tracker-assigned_to" title="<?php echo html_get_tooltip_description('assigned_to') ?>">
		<?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)
		</span></td>
		<td>
		<strong><?php echo _('Priority')._(':'); ?></strong><br />
		<span id="tracker-priority" title="<?php echo html_get_tooltip_description('priority') ?>">
		<?php echo $ah->getPriority(); ?>
		</span></td>
	</tr>

	<?php if (!$ath->usesCustomStatuses()) { ?>
	<tr>
		<td>
			<strong><?php echo _('State')._(':'); ?></strong><br />
			<span id="tracker-status_id" title="<?php echo util_html_secure(html_get_tooltip_description('status_id')) ?>">
			<?php echo $ath->statusBox ('status_id', $ah->getStatusID() ); ?>
			<span>
		</td>
		<td>
		</td>
	</tr>
	<?php } ?>
	<?php
		$ath->renderRelatedTasks($group, $ah);
	?>
	<tr>
		<td colspan="2"><strong><?php echo _('Summary')._(':'); ?><?php echo utils_requiredField(); ?></strong><br />
			<span id="tracker-summary" title="<?php echo html_get_tooltip_description('summary') ?>">
			<?php echo $ah->getSummary(); ?>
			</span>
		</td>
	</tr>
	<tr><td colspan="2">
		<br />
		<?php $ah->showDetails(); ?>
	</td></tr>
</table>
<div id="tabber" class="tabber">
<?php
$count=db_numrows($ah->getMessages());
$nb = $count? ' ('.$count.')' : '';
?>
<div class="tabbertab" title="<?php echo _('Comments').$nb; ?>">
<table width="80%">
	<tr><td colspan="2">
		<br /><strong><?php echo _('Add A Comment') ?>: <?php echo notepad_button('document.forms.trackermodlimitedform.details') ?></strong><br />
		<textarea id="tracker-comment" name="details" rows="7" cols="60" title="<?php echo util_html_secure(html_get_tooltip_description('comment')) ?>"></textarea>
		<p>
		<h2><?php echo _('Comments')._(': ');
echo '</h2>';
$ah->showMessages();
		?>
	</td></tr>
</table>
</div>
<?php
$tabcnt=0;
$file_list = $ah->getFiles();
$count=count($file_list);
$nb = $count? ' ('.$count.')' : '';
?>
<div class="tabbertab" title="<?php echo _('Attachments').$nb; ?>">
<h2><?php echo _('Existing Files')._(':'); ?></h2>
<table width="80%">
	<tr><td colspan="2">
		<?php echo _('Attach Files')._(':'); ?> <?php echo('('._('max upload size: '.human_readable_bytes(util_get_maxuploadfilesize())).')') ?><br />
		<input type="file" name="input_file0" size="30" /><br />
		<input type="file" name="input_file1" size="30" /><br />
		<input type="file" name="input_file2" size="30" /><br />
		<input type="file" name="input_file3" size="30" /><br />
		<input type="file" name="input_file4" size="30" />
		<p>
		<h2><?php echo _('Attached Files')._(':'); ?></h2>
		<?php
		//
		// print a list of files attached to this Artifact
		//
			$ath->renderFiles($group_id, $ah);
		?>
	</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Commits'); ?>">
<table width="80%">
<tr><td colspan="2"><!-- dummy in case the hook is empty --></td></tr>
	<?php
		$hookParams['artifact_id'] = $aid;
		$hookParams['group_id'] = $group_id;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Changes'); ?>">
	<h2><?php echo _('Changes') ?></h2>
	<?php $ah->showHistory(); ?>
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
