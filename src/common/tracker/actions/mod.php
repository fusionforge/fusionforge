<?php
/**
 * Tracker Facility
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) Franck Villaume - Capgemini
 * Copyright (C) 2011 Alain Peyrat - Alcatel-Lucent
 * Copyright 2012, Thorsten “mirabilos” Glaser <t.glaser@tarent.de>
 * Copyright 2012-2016, Franck Villaume - TrivialDev
 * Copyright 2016, Stéphane-Eymeric Bredthauer - TrivialDev
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
global $group;
global $aid;
global $atid;
global $HTML;

function gettipspan($idpart, $content) {
	$id = 'tracker-' . str_replace(' ', '_', $idpart);
	return '<span id="' . $id . '" title="' .
	html_get_tooltip_description($idpart) . '">' .
	$content . '</span>';
}

html_use_jqueryui();
html_use_coolfieldset();
html_use_jquerydatetimepicker();
$ath->header(array('title'=> $ah->getStringID().' '. $ah->getSummary(), 'atid'=>$ath->getID()));

echo notepad_func();
echo init_datetimepicker();

echo $HTML->openForm(array('id' => 'trackermodform', 'action' => '/tracker/?group_id='.$group_id.'&atid='.$ath->getID(), 'enctype' => 'multipart/form-data', 'method' => 'post'));
?>
<input type="hidden" name="form_key" value="<?php echo form_generate_key(); ?>" />
<input type="hidden" name="func" value="postmod" />
<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>" />

<?php
if (session_loggedin()) {
echo $HTML->listTableTop(array(), array(), 'full');
?>
		<tr>
			<td><?php
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
			<td><?php
				if ($group->usesPM()) {
					echo util_make_link('/tracker/?func=taskmgr&group_id='.$group_id.'&atid='.$atid.'&aid='.$aid, html_image('ic/taskman20w.png', 20, 20).'<strong>'._('Build Task Relation').'</strong>');
				}
				?>
			</td>
			<td>
				<?php
				echo util_make_link('/tracker/?func=deleteartifact&aid='.$aid.'&group_id='.$group_id.'&atid='.$atid, $HTML->getDeletePic().html_e('strong', array(), _('Delete')));
				?>
			</td>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
<?php echo $HTML->listTableBottom(); ?>
<br />
<?php } ?>
<?php echo $HTML->listTableTop(array(), array(), 'full'); ?>
	<tr>
		<td>
			<strong><?php echo _('Submitted by')._(':'); ?></strong><br />
			<?php echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				$submittedBy = $ah->getSubmittedBy();
				?>
				(<samp><?php echo util_make_link_u($submittedUnixName,$submittedBy,$submittedUnixName); ?></samp>)
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

	<tr>
		<td><strong><?php echo _('Data Type'). _(': ') ?></strong><br />
<?php

$atf = new ArtifactTypeFactory($group);
$tids = array();
foreach ($atf->getArtifactTypes() as $at) {
	if (forge_check_perm('tracker', $at->getID(), 'manager')) {
		$tids[] = $at->getID();
	}
}

$res = db_query_params('SELECT group_artifact_id, name
			FROM artifact_group_list
			WHERE group_artifact_id = ANY ($1)',
			array(db_int_array_to_any_clause($tids)));

echo html_build_select_box($res,'new_artifact_type_id',$ath->getID(),false);

?>
		</td>
		<td>
		</td>
	</tr>
	<?php
		$ath->renderExtraFields($ah->getExtraFieldData(),true,'none',false,'Any',array(),false,'UPDATE');
		$fieldInFormula = $ath->getFieldsInFormula();
	?>
	<tr>
		<td><strong><?php echo _('Assigned to')._(': ') ?></strong><br />
		<?php
		if (in_array('assigned_to', $fieldInFormula)) {
			echo $ath->technicianBox('assigned_to', $ah->getAssignedTo(), true, 'none', -1, '', false, array('class'=>'in-formula'));
		} else {
			echo $ath->technicianBox('assigned_to', $ah->getAssignedTo());
		}
		echo " ";
		echo util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID().'&update_users=1', '('._('Admin').')');
		?>
		</td><td>
		<strong><?php echo _('Priority'). _(': ') ?></strong><br />
		<?php
		if (in_array('priority', $fieldInFormula)) {
			echo $ath->priorityBox('priority',$ah->getPriority(), false, array('class'=>'in-formula'));
		} else {
			echo $ath->priorityBox('priority',$ah->getPriority());
		}
		?>
		</td>
	</tr>

	<?php if (!$ath->usesCustomStatuses()) { ?>
	<tr>
		<td>
			<strong><?php echo _('State')._(': ') ?></strong><br />
			<?php
				if (in_array('status', $fieldInFormula)) {
					echo $ath->statusBox('status_id', $ah->getStatusID(), false, 'none', array('class'=>'in-formula'));
				} else {
					echo $ath->statusBox('status_id', $ah->getStatusID());
				}
			?>
		</td>
		<td>
		</td>
	</tr>
	<?php }
	?>
	<tr>
		<td colspan="2"><strong><?php echo _('Summary').utils_requiredField()._(':') ?></strong><br />
		<?php if (in_array('summary', $fieldInFormula)) { ?>
			<input id="tracker-summary" required="required" title="<?php echo _('The summary text-box represents a short tracker item summary. Useful when browsing through several tracker items.') ?>" type="text" name="summary" size="70" value="<?php
				echo $ah->getSummary();
				?>" maxlength="255" class="in-formula"/>
		<?php } else { ?>
			<input id="tracker-summary" required="required" title="<?php echo _('The summary text-box represents a short tracker item summary. Useful when browsing through several tracker items.') ?>" type="text" name="summary" size="70" value="<?php
				echo $ah->getSummary();
				?>" maxlength="255" />
		<?php } ?>
		</td>
	</tr>
	<tr><td colspan="2">

		<?php
			if (in_array('description', $fieldInFormula)) {
				echo $ah->showDetails(true, array('class'=>'in-formula'));
			} else {
				echo $ah->showDetails(true);
			}
		?>

	</td></tr>
<?php echo $HTML->listTableBottom(); ?>
<div id="tabber" >
<?php
$count=db_numrows($ah->getMessages());
$nb = $count? ' ('.$count.')' : '';
$file_list = $ah->getFiles();
$count=count($file_list);
$nbf = $count? ' ('.$count.')' : '';
$pm = plugin_manager_get_object();
$pluginsListeners = $pm->GetHookListeners('artifact_extra_detail');
$pluginfound = false;
foreach ($pluginsListeners as $pluginsListener) {
	if ($ath->Group->usesPlugin($pluginsListener)) {
		$pluginfound = true;
		break;
	}
}
$count=db_numrows($ah->getHistory());
$nbh = $count? ' ('.$count.')' : '';
?>
	<ul>
	<li><a href="#tabber-comments"><?php echo _('Comments').$nb; ?></a></li>
	<?php if ($group->usesPM()) {
		$count= db_numrows($ah->getRelatedTasks());
		$nbrt = $count? ' ('.$count.')' : '';
	?>
	<li><a href="#tabber-tasks"><?php echo _('Related Tasks').$nbrt; ?></a></li>
	<?php } ?>
	<li><a href="#tabber-attachments"><?php echo _('Attachments').$nbf; ?></a></li>
	<?php if ($pluginfound) { ?>
	<li><a href="#tabber-commits"><?php echo _('Commits'); ?></a></li>
	<?php } ?>
	<li><a href="#tabber-changes"><?php echo _('Changes').$nbh; ?></a></li>
	<?php if ($ah->hasRelations()) {
		$count=db_numrows($ah->getRelations());
		$nbr = $count? ' ('.$count.')' : '';
	?>
	<li><a href="#tabber-relations"><?php echo _('Relations').$nbr; ?></a></li>
	<?php } ?>
	<?php if (forge_get_config('use_artefacts_dependencies')) {
		$countC=$ah->hasChildren()?$ah->hasChildren():0;
		$countP=$ah->hasParent()?1:0;
		$nbd = $countC+$countP? ' ('.$countP.'/'.$countC.')' : '';
	?>
	<li><a href="#tabber-dependencies"><?php echo _('Dependencies').$nbd; ?></a></li>
	<?php } ?>
	<?php if (forge_get_config('use_object_associations')) {
		$anf = '';
		if ($ah->getAssociationCounter()) {
			$anf = ' ('.$ah->getAssociationCounter().')';
		} ?>
	<li><a href="#tabber-object-associations"><?php echo _('Associations').$anf; ?></a></li>
	<?php } ?>
	</ul>
<div id="tabber-comments" class="tabbertab">
<?php echo $HTML->listTableTop(); ?>
	<tr><td colspan="2">
		<br /><strong><?php echo _('Use Canned Response')._(':'); ?></strong><br />
		<?php
		echo $ath->cannedResponseBox('tracker-canned_response');
		echo " ";
		echo util_make_link('/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID().'&add_canned=1', '('._('Admin').')');
		?>
		<script type="text/javascript">/* <![CDATA[ */
			$('#tracker-canned_response').change(function() {
				$.ajax({
					type: 'POST',
					url: 'index.php',
					data: 'rtype=ajax&function=get_canned_response&group_id=<?php echo $group_id ?>&canned_response_id='+$('#tracker-canned_response').val(),
					success: function(rep){
						// the following line is not the best but works with IE6
						$('#tracker-canned_response option').each(function() {$(this).attr("selected", "selected"); return false;});
						if ($('#tracker-comment').val()) {
							rep = "\n" + rep
						}
						$('#tracker-comment').val($('#tracker-comment').val() + rep);
					}
				});
			});
		/* ]]> */</script>
		<p>
		<strong><?php echo _('Post Comment')._(': ') ?><?php echo notepad_button('document.forms.trackermodform.details') ?></strong><br />
		<textarea id="tracker-comment" name="details" rows="7" style="width: 100%; box-sizing: border-box;" title="<?php echo util_html_secure(html_get_tooltip_description('comment')) ?>"></textarea></p>
		<?php echo $ah->showMessages(); ?>
	</td></tr>
<?php echo $HTML->listTableBottom(); ?>
</div>
<?php
if ($group->usesPM()) {
?>
<div id="tabber-tasks" class="tabbertab">
	<?php
		echo $ath->renderRelatedTasks($group, $ah);
	?>
</div>
<?php }
?>
<div id="tabber-attachments" class="tabbertab">
<?php echo $HTML->listTableTop(); ?>
	<tr><td colspan="2">
        <strong><?php echo _('Attach Files')._(':'); ?></strong> <?php echo('('._('max upload size')._(': ').human_readable_bytes(util_get_maxuploadfilesize()).')') ?><br />
        <input type="file" name="input_file0" size="30" /><br />
        <input type="file" name="input_file1" size="30" /><br />
        <input type="file" name="input_file2" size="30" /><br />
        <input type="file" name="input_file3" size="30" /><br />
        <input type="file" name="input_file4" size="30" /><br />
	</td></tr>
<?php echo $HTML->listTableBottom();
//
// print a list of files attached to this Artifact
//
echo $ath->renderFiles($group_id, $ah);
?>
</div>
<?php if ($pluginfound) { ?>
<div id="tabber-commits" class="tabbertab">
<?php echo $HTML->listTableTop(); ?>
<tr><td colspan="2"><!-- dummy in case the hook is empty --></td></tr>
	<?php
		$hookParams['artifact_id'] = $aid;
		$hookParams['group_id'] = $group_id;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
<?php echo $HTML->listTableBottom(); ?>
</div>
<?php } ?>
<div id="tabber-changes" class="tabbertab">
	<?php echo $ah->showHistory(); ?>
</div>
<?php if ($ah->hasRelations()) { ?>
<div id="tabber-relations" class="tabbertab">
	<?php echo $ah->showRelations(); ?>
</div><?php
}
if (forge_get_config('use_artefacts_dependencies')) { ?>
	<div id="tabber-dependencies" class="tabbertab">
		<?php echo $ah->showDependencies(); ?>
	</div><?php
}
if (forge_get_config('use_object_associations')) { ?>
	<div id="tabber-object-associations" class="tabbertab">
	<?php if (forge_check_perm('tracker',$ath->getID(),'submit')) {
			echo $ah->showAssociations('/tracker/?func=removeassoc&aid='.$ah->getID().'&group_id='.$ath->Group->getID().'&atid='.$ath->getID());
			echo $ah->showAddAssociations();
		} else {
			echo $ah->showAssociations();
		} ?>
	</div>
	<?php } ?>
</div>
<?php if (session_loggedin()) {
	echo $HTML->listTableTop(); ?>
		<tr>
			<td>
				<input type="submit" name="submit" value="<?php echo _('Save Changes') ?>" />
			</td>
		</tr>
	<?php echo $HTML->listTableBottom();
}
echo $HTML->closeForm();
$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
