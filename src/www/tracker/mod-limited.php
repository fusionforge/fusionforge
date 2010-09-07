<?php
/**
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 */


$ath->header(array 
	     ('title' => _('Modify').' [#'.$ah->getID(). '] ' 
	      . util_unconvert_htmlspecialchars($ah->getSummary()),
	      'atid'=>$ath->getID()));

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
				} else {
					$img="mail16w.png";
					$key="monitor";
				}
				echo '
				<a href="index.php?group_id='.$group_id.'&amp;artifact_id='.$ah->getID().'&amp;atid='.$ath->getID().'&amp;func=monitor"><strong>'.
					html_image('ic/'.$img.'','20','20').' '.$key.'</strong></a>';
				?>&nbsp;<a href="javascript:help_window('/help/tracker.php?helpname=monitor')"><strong>(?)</strong></a>
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
				<input type="submit" name="submit" value="<?php echo _('Submit') ?>" />
			</td>
		</tr>
</table>
<p/>
<?php } ?>
<table border="0" width="80%">

	<tr>
		<td><strong><?php echo _('Submitted by') ?>:</strong><br />
			<?php
			echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				$submittedBy = $ah->getSubmittedBy();
				?>
				(<tt><?php echo util_make_link ($submittedUnixName,$submittedBy,$submittedUnixName); ?></tt>)
			<?php } ?>
		</td>
		<td><strong><?php echo _('Date Submitted') ?>:</strong><br />
		<?php
		echo date(_('Y-m-d H:i'), $ah->getOpenDate() );

		$close_date = $ah->getCloseDate();
		if ($ah->getStatusID()==2 && $close_date > 1) {
			echo '<br /><strong>'._('Date Closed').':</strong><br />'
				 .date(_('Y-m-d H:i'), $close_date);
		}
		?>
		</td>
	</tr>

    <?php
		$ath->renderExtraFields($ah->getExtraFieldData(),true,'none',false,'Any','',false,'UPDATE');
		?>

	<tr>
		<td><strong><?php echo _('Assigned to')?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=assignee'); ?>')"><strong>(?)</strong></a></strong><br />
            <?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</td>
		<td>
		<strong><?php echo _('Priority') ?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=priority'); ?>')"><strong>(?)</strong></a></strong><br />
		<?php
		/*
			Priority of this request
		*/
		echo $ah->getPriority();
		?>
		</td>
	</tr>

	<tr>
		<td>
		<?php if (!$ath->usesCustomStatuses()) { ?>
		<strong><?php echo _('State') ?>: <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=status'); ?>')"><strong>(?)</strong></a></strong><br />
		<?php

		echo $ath->statusBox ('status_id', $ah->getStatusID() );
		}
		?>
		</td>
		<td>
		</td>
	</tr>
	<?php
		$ath->renderRelatedTasks($group, $ah);
		$ath->renderFiles($group_id, $ah);
	?>
	<tr>
		<td colspan="2"><strong><?php echo _('Summary')?><?php echo utils_requiredField(); ?>: <a href="javascript:help_window('/help/tracker.php?helpname=summary')"><strong>(?)</strong></a></strong><br />
			<?php echo $ah->getSummary(); ?>
		</td>
	</tr>

	<tr><td colspan="2">
		<br />
		<?php echo $ah->showDetails(); ?>
	</td></tr>
</table>
<script type="text/javascript" src="<?php echo util_make_uri('/tabber/tabber.js') ?>"></script>
<div id="tabber" class="tabber">
<div class="tabbertab" title="<?php echo _('Followups');?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<br /><strong><?php echo _('OR Attach A Comment') ?>: <?php echo notepad_button('document.forms.trackermodlimitedform.details') ?> <a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=comment'); ?>')"><strong>(?)</strong></a></strong><br />
		<textarea name="details" rows="7" cols="60"></textarea>
		<p>
		<h2><?php echo _('Followup') ?>:</h2>
		<?php
			echo $ah->showMessages(); 
		?>
	</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Attachments'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<?php echo _('Attach Files') ?><br />
		<input type="file" name="input_file0" size="30" /><br />
		<input type="file" name="input_file1" size="30" /><br />
		<input type="file" name="input_file2" size="30" /><br />
		<input type="file" name="input_file3" size="30" /><br />
		<input type="file" name="input_file4" size="30" /><br />
		<p>
		<h2><?php echo _('Attached Files') ?>:</h2>
		<?php
		//
		//  print a list of files attached to this Artifact
		//
		$file_list =& $ah->getFiles();

		$count=count($file_list);

		$title_arr=array();
		$title_arr[]=_('Delete');
		$title_arr[]=_('Name');
		$title_arr[]=_('Description');
		$title_arr[]=_('Download');
		echo $GLOBALS['HTML']->listTableTop ($title_arr);

		if ($count > 0) {

			for ($i=0; $i<$count; $i++) {
				echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
				<td><input type="checkbox" name="delete_file[]" value="'. $file_list[$i]->getID() .'" />'._('Delete').' </td>
				<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
				<td>'.  htmlspecialchars($file_list[$i]->getDescription()) .'</td>
				<td>'.util_make_link ('/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'.$file_list[$i]->getName(),_('Download')).'</td>
				</tr>';
			}

		} else {
			echo '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle(0).'><td colspan="4">'._('No Files Currently Attached').'</td></tr>';
		}
		echo $GLOBALS['HTML']->listTableBottom();
		?>
	</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Commits'); ?>">
<table border="0" width="80%">
	<?php
		$hookParams['artifact_id']=$aid;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Changes'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
		<h2><?php echo _('Change Log') ?>:</h2>
		<?php 
			echo $ah->showHistory(); 
		?>
	</td></tr>
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
