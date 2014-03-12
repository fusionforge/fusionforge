<?php
/**
 * Tracker Detail
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

global $ath;
global $ah;
global $group_id;
global $aid;

use_javascript('/tabber/tabber.js');
html_use_coolfieldset();

$ath->header(array ('title'=> $ah->getStringID().' '. $ah->getSummary(), 'atid'=>$ath->getID()));

echo notepad_func();

?>
	<form id="trackerdetailform" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;atid=<?php echo $ath->getID(); ?>" method="post" enctype="multipart/form-data">

<?php if (session_loggedin()) { ?>
	<table class="fullwidth">
		<tr>
			<td>
				<?php
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
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
	</table>
<?php } ?>
	<table width="80%">
		<tr>
			<td>
				<strong><?php echo _('Date')._(':'); ?></strong><br />
				<?php echo date( _('Y-m-d H:i'), $ah->getOpenDate() ); ?>
			</td>
			<td>
				<strong><?php echo _('Priority')._(':'); ?></strong><br />
				<?php echo $ah->getPriority(); ?>
			</td>
		</tr>

		<tr>
			<td>
				<strong><?php echo _('State')._(':'); ?></strong><br />
				<?php echo $ah->getStatusName(); ?>
			</td>
			<td></td>
		</tr>
		<tr>
	        <td>
			<strong><?php echo _('Submitted by')._(':'); ?></strong><br />
			<?php echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				$submittedBy = $ah->getSubmittedBy();
				?>
				(<tt><?php echo util_make_link_u ($submittedUnixName,$submittedBy,$submittedUnixName); ?></tt>)
			<?php } ?>
			</td>
			<td>
				<strong><?php echo _('Assigned to')._(':'); ?></strong><br />
				<?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)
			</td>
		</tr>

		<?php
			$ath->renderExtraFields($ah->getExtraFieldData(),true,'none',false,'Any',array(),false,'DISPLAY');
			$ath->renderRelatedTasks($group, $ah);
		?>

		<tr>
			<td colspan="2">
				<strong><?php echo _('Summary')._(':'); ?></strong><br />
				<?php echo $ah->getSummary(); ?>
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
			<?php if (forge_check_perm ('tracker',$ath->getID(),'submit')) { ?>
			<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
			<input type="hidden" name="func" value="postmod" />
			<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
			<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>" />
			<p>
			<strong><?php echo _('Add A Comment')._(':'); ?></strong>
			<?php echo notepad_button('document.forms.trackerdetailform.details') ?><br />
			<textarea name="details" rows="10" cols="60"></textarea>
			</p>
			<?php } ?>
		</td></tr>
		<tr><td colspan="2">
		<h2><?php echo _('Comments')._(': '); ?></h2>
		<?php $ah->showMessages(); ?>
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
<table width="80%">
	<tr><td colspan="2">
	<?php if (session_loggedin() && ($ah->getSubmittedBy() == user_getid())) { ?>
		<strong><?php echo _('Attach Files')._(':'); ?></strong>  <?php echo('('._('max upload size: '.human_readable_bytes(util_get_maxuploadfilesize())).')') ?><br />
		<input type="file" name="input_file0" /><br />
		<input type="file" name="input_file1" /><br />
		<input type="file" name="input_file2" /><br />
		<input type="file" name="input_file3" /><br />
		<input type="file" name="input_file4" /><br />
	<?php } ?>
	<h2><?php echo _('Attached Files')._(':'); ?></h2>
	</td></tr>
<?php
	//
	// print a list of files attached to this Artifact
	//
		$ath->renderFiles($group_id, $ah);
	?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Commits'); ?>" >
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
