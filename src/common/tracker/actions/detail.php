<?php
/**
 * Tracker Detail
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012-2015, Franck Villaume - TrivialDev
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
global $HTML;

html_use_jqueryui();
html_use_coolfieldset();

$ath->header(array ('title'=> $ah->getStringID().' '. $ah->getSummary(), 'atid'=>$ath->getID()));

echo notepad_func();

?>

<script type="text/javascript">//<![CDATA[
jQuery(document).ready(function() {
	jQuery("#tabber").tabs();
});
//]]></script>

<?php
echo $HTML->openForm(array('id' => 'trackerdetailform', 'action' => '/tracker/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post', 'enctype' => 'multipart/form-data'));
if (session_loggedin()) { ?>
	<table class="fullwidth">
		<tr>
			<td>
				<?php
					if ($ah->isMonitoring()) {
						$img="xmail16w.png";
						$text=_('Stop monitoring');
					} else {
						$img="mail16w.png";
						$text=_('Monitor');
					}
					echo util_make_link('/tracker/?group_id='.$group_id.'&artifact_id='.$ah->getID().'&atid='.$ath->getID().'&func=monitor', html_e('strong', array(), html_image('ic/'.$img, 20, 20).' '.$text), array('id' => 'tracker-monitor', 'title' => util_html_secure(html_get_tooltip_description('monitor'))));
					?>
			</td>
			<td><?php
					$votes = $ah->getVotes();
					echo html_e('span', array('id' => 'tracker-votes', 'title' => html_get_tooltip_description('votes')), html_e('strong', array(), _('Votes') . _(': ')).sprintf('%1$d/%2$d (%3$d%%)', $votes[0], $votes[1], $votes[2]));
					if ($ath->canVote()) {
						if ($ah->hasVote()) {
							$key = 'pointer_down';
							$txt = _('Retract Vote');
						} else {
							$key = 'pointer_up';
							$txt = _('Cast Vote');
						}
						echo util_make_link('/tracker/?group_id='.$group_id.'&aid='.$ah->getID().'&atid='.$ath->getID().'&func='.$key, html_image('ic/'.$key.'.png', 16, 16), array('id' => 'tracker-vote', 'alt' => $txt, 'title' => util_html_secure(html_get_tooltip_description('vote'))));
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
				(<samp><?php echo util_make_link_u ($submittedUnixName,$submittedBy,$submittedUnixName); ?></samp>)
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
<?php
$count=db_numrows($ah->getMessages());
$nb = $count? ' ('.$count.')' : '';
$pm = plugin_manager_get_object();
$pluginsListeners = $pm->GetHookListeners('artifact_extra_detail');
$pluginfound = false;
foreach ($pluginsListeners as $pluginsListener) {
	if ($ath->Group->usesPlugin($pluginsListener)) {
		$pluginfound = true;
		break;
	}
}
?>
<div id="tabber">
	<ul>
	<li><a href="#tabber-comments"><?php echo _('Comments'); ?></a></li>
	<li><a href="#tabber-attachments"><?php echo _('Attachments'); ?></a></li>
	<?php if ($pluginfound) { ?>
	<li><a href="#tabber-commits"><?php echo _('Commits'); ?></a></li>
	<?php } ?>
	<li><a href="#tabber-changes"><?php echo _('Changes'); ?></a></li>
	<?php if ($ah->hasRelations()) { ?>
	<li><a href="#tabber-relations"><?php echo _('Relations'); ?></a></li>
	<?php } ?>
	</ul>
	<div id="tabber-comments" class="tabbertab" title="<?php echo _('Comments').$nb; ?>">
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
	<div id="tabber-attachments" class="tabbertab" title="<?php echo _('Attachments').$nb; ?>">
	<table width="80%">
		<tr><td colspan="2">
		<?php if (session_loggedin() && ($ah->getSubmittedBy() == user_getid())) { ?>
			<strong><?php echo _('Attach Files')._(':'); ?></strong>  <?php echo('('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')') ?><br />
			<input type="file" name="input_file0" /><br />
			<input type="file" name="input_file1" /><br />
			<input type="file" name="input_file2" /><br />
			<input type="file" name="input_file3" /><br />
			<input type="file" name="input_file4" /><br />
		<?php } ?>
		<?php
		//
		// print a list of files attached to this Artifact
		//
			$ath->renderFiles($group_id, $ah);
		?>
		</td></tr>
	</table>
	</div>
<?php
	if ($pluginfound) {
?>
	<div id="tabber-commits" class="tabbertab" title="<?php echo _('Commits'); ?>" >
	<table width="80%">
	<tr><td colspan="2"><!-- dummy in case the hook is empty --></td></tr>
		<?php
			$hookParams['artifact_id'] = $aid;
			$hookParams['group_id'] = $group_id;
			plugin_hook("artifact_extra_detail",$hookParams);
		?>
	</table>
	</div>
<?php
	}
?>
	<div id="tabber-changes" class="tabbertab" title="<?php echo _('Changes'); ?>">
		<?php $ah->showHistory(); ?>
	</div>
	<?php $ah->showRelations(); ?>
</div>
<?php if (session_loggedin()) { ?>
	<table class="fullwidth">
		<tr>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
	</table>
<?php }
echo $HTML->closeForm();
$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
