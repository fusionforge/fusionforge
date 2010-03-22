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
	     ('title' => _('Detail').': [#'.$ah->getID(). '] ' 
	      . util_unconvert_htmlspecialchars($ah->getSummary()),
	      'atid'=>$ath->getID()));

echo notepad_func();

?>
	<h1>[#<?php echo $ah->getID(); ?>] <?php echo util_unconvert_htmlspecialchars($ah->getSummary()); ?></h1>

	<form id="trackerdetailform" action="<?php echo getStringFromServer('PHP_SELF'); ?>?group_id=<?php echo $group_id; ?>&amp;atid=<?php echo $ath->getID(); ?>" method="post" enctype="multipart/form-data">

<?php if (session_loggedin()) { ?>
	<table cellpadding="0" width="100%">
		<tr>
			<td>
				<?php 
					if ($ah->isMonitoring()) {
						$img="xmail16w.png";
						$key="monitorstop";
					} else {
						$img="mail16w.png";
						$key="monitor";
					}
					echo '
					<a href="index.php?group_id='.$group_id.'&amp;artifact_id='.$ah->getID().'&amp;atid='.$ath->getID().'&amp;func=monitor"><strong>'.
						html_image('ic/'.$img.'','20','20',array()).' '.$key.'</strong></a>';
					?>&nbsp;<a href="javascript:help_window('<?php echo util_make_url ('/help/tracker.php?helpname=monitor'); ?>')"><strong>(?)</strong></a>
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
			$ath->renderExtraFields($ah->getExtraFieldData(),true,'none',false,'Any','',false,'DISPLAY');
			$ath->renderRelatedTasks($group, $ah);
			$ath->renderFiles($group_id, $ah);
		?>

		<tr><td colspan="2"><strong><?php echo _('Summary') ?>:</strong><br /><?php echo $ah->getSummary(); ?></td></tr>

		<tr><td colspan="2">
			<br />
			<?php echo $ah->showDetails(); ?>
		</td></tr>
</table>
<script type="text/javascript" src="<?php echo util_make_uri('/tabber/tabber.js') ?>"></script>

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
		<h2><?php echo _('Followup') ?></h2>
		<?php
		echo $ah->showMessages();
		?>
		</td></tr>
</table>
</div>
<div class="tabbertab" title="<?php echo _('Attachments'); ?>">
<table border="0" width="80%">
	<tr><td colspan="2">
	<?php if (session_loggedin() && ($ah->getSubmittedBy() == user_getid())) { ?>
		<strong><?php echo _('Attach Files'); ?></strong><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<input type="file" name="input_file[]" size="30" /><br />
		<p />
	<?php } ?>
	<h2><?php echo _('Attached Files') ?>:</h2>
	<?php
	//
	//  print a list of files attached to this Artifact
	//
	$file_list =& $ah->getFiles();

	$count=count($file_list);

	if ($count > 0) {

		$title_arr=array();
		$title_arr[]=_('Name');
		$title_arr[]=_('Download');
		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		for ($i=0; $i<$count; $i++) {
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
			<td>'.util_make_link ('/tracker/download.php/'.$group_id.'/'.$ath->getID().'/'.$ah->getID().'/'.$file_list[$i]->getID().'/'.$file_list[$i]->getName(),_('Download')).'</td>
			</tr>';
		}
		echo $GLOBALS['HTML']->listTableBottom();

	} else {
		echo _('No Files Currently Attached');
	}
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
