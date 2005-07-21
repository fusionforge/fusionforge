<?php
/**
 * SourceForge Generic Tracker facility
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

echo $ath->header(array ('title'=>$Language->getText('tracker_detail','title').': '.$ah->getID(). ' '.util_unconvert_htmlspecialchars($ah->getSummary()),'pagename'=>'tracker_detail','atid'=>$ath->getID(),'sectionvals'=>array($ath->getName())));

echo notepad_func();

?>
	<h2>[#<?php echo $ah->getID(); ?>] <?php echo util_unconvert_htmlspecialchars($ah->getSummary()); ?></h2>

	<table cellpadding="0" width="100%">
		<tr>
		<form action="<?php echo $PHP_SELF; ?>?group_id=<?php echo $group_id; ?>&atid=<?php echo $ath->getID(); ?>" method="post" enctype="multipart/form-data">

			<td><?php
				if (session_loggedin()) {

					if ($ah->isMonitoring()) {
						$img="xmail16w.png";
						$key="stop_monitoring";
					} else {
						$img="mail16w.png";
						$key="monitor";
					}
					echo '
					<a href="index.php?group_id='.$group_id.'&artifact_id='.$ah->getID().'&atid='.$ath->getID().'&func=monitor"><strong>'.
						html_image('ic/'.$img.'','20','20',array()).' '.$Language->getText('tracker_utils',$key).'</strong></a>';
					?>&nbsp;<a href="javascript:help_window('/help/tracker.php?helpname=monitor')"><strong>(?)</strong></a>

				<?php } else { ?>

				<h3><FONT COLOR="RED">
				<?php echo $Language->getText('tracker','please_login',array('<a href="/account/login.php?return_to='.urlencode($REQUEST_URI).'">','</a>')) ?></FONT></h3>
                <?php if ($ath->allowsAnon()) { ?>
                <?php echo $Language->getText('tracker','insert_email') ?>
                <br />
                <p>
                <input type="TEXT" name="user_email" SIZE="20" MAXLENGTH="40">
                <?php } ?>

				<?php } ?>
		<p>
			</td>
			<!-- <td><?php /* if (!$ath->usesCustomStatuses()) { ?><strong><?php echo $Language->getText('tracker','status') ?>:</strong><br /><?php echo $ah->getStatusName(); }*/ ?></td> -->
			<td><strong><?php echo $Language->getText('tracker','status') ?>:</strong><br /><?php echo $ah->getStatusName(); ?></td>
		</tr>
		<tr>
			<td><strong><?php echo $Language->getText('tracker','date') ?>:</strong><br /><?php echo date( $sys_datefmt, $ah->getOpenDate() ); ?></td>
			<td><strong><?php echo $Language->getText('tracker','priority') ?>:</strong><br /><?php echo $ah->getPriority(); ?></td>
		</tr>

		<tr>
			<td><strong><?php echo $Language->getText('tracker','submitted_by') ?>:</strong><br />
			<?php
			echo $ah->getSubmittedRealName();
			if($ah->getSubmittedBy() != 100) {
				$submittedUnixName = $ah->getSubmittedUnixName();
				?>
				(<tt><a href="/users/<?php echo $submittedUnixName; ?>"><?php echo $submittedUnixName; ?></a></tt>)
			<?php } ?>
			</td>
			<td><strong><?php echo $Language->getText('tracker','assigned_to') ?>:</strong><br />
			<?php echo $ah->getAssignedRealName(); ?> (<?php echo $ah->getAssignedUnixName(); ?>)</td>
		</tr>

	<?php
		//$ath->renderExtraFields($ah->getExtraFieldData(),true);
	?>

		<tr><td colspan="2"><strong><?php echo $Language->getText('tracker','summary') ?>:</strong><br /><?php echo $ah->getSummary(); ?></td></tr>

		<tr><td colspan="2">
			<br />
			<?php echo $ah->showDetails(); ?>
            <?php if ($ath->allowsAnon() || session_loggedin()) { ?>

			<input type="hidden" name="func" value="postmod">
			<input type="hidden" name="artifact_id" value="<?php echo $ah->getID(); ?>">
			<p>
			<strong><?php echo $Language->getText('tracker_detail','add_comment') ?>:</strong> 
			<?php echo notepad_button('document.forms[1].details') ?><br />
			<textarea name="details" ROWS="10" COLS="60" WRAP="SOFT"></textarea>
            <?php } ?>
		</td></tr>

	<tr><td colspan="2">
	<h3><?php echo $Language->getText('tracker','followups') ?></h3>
	<p>
	<?php

	echo $ah->showMessages();

	?>
	</td></tr>

	<tr><td colspan=2>
	<?php if (session_loggedin() && ($ah->getSubmittedBy() == user_getid())) { ?>
		<strong><?php echo $Language->getText('tracker','check_upload') ?>:</strong> <input type="checkbox" name="add_file" value="1" /><br />
		<input type="file" name="input_file" size="30" /></p>
		<p>
		<strong><?php echo $Language->getText('tracker','file_description') ?>:</strong><br />
		<input type="text" name="file_description" size="40" maxlength="255" /></p>
	<?php } ?>
	<h4><?php echo $Language->getText('tracker_detail','attached_files') ?>:</h4>
	<?php
	//
	//  print a list of files attached to this Artifact
	//
	$file_list =& $ah->getFiles();

	$count=count($file_list);

	$title_arr=array();
	$title_arr[]=$Language->getText('tracker_detail','name');
	$title_arr[]=$Language->getText('tracker_detail','description');
	$title_arr[]=$Language->getText('tracker_detail','download');
	echo $GLOBALS['HTML']->listTableTop ($title_arr);

	if ($count > 0) {

		for ($i=0; $i<$count; $i++) {
			echo '<tr '. $GLOBALS['HTML']->boxGetAltRowStyle($i) .'>
			<td>'. htmlspecialchars($file_list[$i]->getName()) .'</td>
			<td>'.  htmlspecialchars($file_list[$i]->getDescription()) .'</td>
			<td><a href="/tracker/download.php/'.$group_id.'/'. $ath->getID().'/'. $ah->getID() .'/'.$file_list[$i]->getID().'/'. $file_list[$i]->getName() .'">'.$Language->getText('tracker_detail','download').'</a></td>
			</tr>';
		}

	} else {
		echo '<tr '.$GLOBALS['HTML']->boxGetAltRowStyle(0).'><td colspan="3">'.$Language->getText('tracker_detail','no_files_attached').'</td></tr>';
	}
	
	echo $GLOBALS['HTML']->listTableBottom();

	?>
	</td></tr>
	<?php if ($ath->allowsAnon() || session_loggedin()) { ?>
	<tr><td colspan="2">
		<h3><?php echo $Language->getText('tracker_detail','security_note') ?></h3>
		<p>
		<input type="submit" name="submit" value="<?php echo $Language->getText('general','submit') ?>">
		</form>
	</td></tr>
	<?php } ?>

	<?php
		$hookParams['artifact_id']=$aid;
		plugin_hook("artifact_extra_detail",$hookParams);
	?>
	<tr>
	<td colspan="2">
	<h3><?php echo $Language->getText('tracker_detail','changes') ?>:</h3>
	<p>
	<?php

	echo $ah->showHistory();

	?>
	</td>
	</tr>
</table>
<?php

$ath->footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
