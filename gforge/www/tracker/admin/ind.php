<?php

	//
	//
	//	This page lists the available trackers as well as a 
	//	form to create a new tracker
	//
	//


	//
	//  get the Group object
	//
	$group =& group_get_object($group_id);
	if (!$group || !is_object($group)) {
		exit_error('Error','Could Not Get Group Object');
	} elseif ($group->isError()) {
		exit_error('Error',$group->getErrorMessage());
	}

	$perm =& $group->getPermission( session_get_user() );

	if ($post_changes) {

		if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
			exit_permission_denied();
		}

		if ($add_at) {
			$res=new ArtifactTypeHtml($group);
			if (!$res->create($name,$description,$is_public,$allow_anon,$email_all,$email_address,
				$due_period,$use_resolution,$submit_instructions,$browse_instructions)) {
				$feedback .= $res->getErrorMessage();
			} else {
				header ("Location: /tracker/admin/?group_id=$group_id&atid=".$res->getID()."&update_users=1");
			}

		}
	}


	//
	//	Display existing artifact types
	//
	$atf = new ArtifactTypeFactory($group);
	if (!$group || !is_object($group) || $group->isError()) {
		exit_error('Error','Could Not Get ArtifactTypeFactory');
	}

	$at_arr =& $atf->getArtifactTypes();

	//required params for site_project_header();
	$params['group']=$group_id;
	$params['toptab']='tracker';
	$params['title'] = $page_title;

	echo site_project_header($params);
	echo $HTML->subMenu(
		array(
			$Language->getText('tracker','reporting'),
			$Language->getText('tracker','admin')
		),
		array(
			'/tracker/reporting/?group_id='.$group_id,
			'/tracker/admin/?group_id='.$group_id
		)
	);

	if (!$at_arr || count($at_arr) < 1) {
		echo "<h1>".$Language->getText('tracker_admin','no_trackers_found')."</h1>";
		echo "<p>&nbsp;</p>";
	} else {

		echo '
		<p>'.$Language->getText('tracker_admin','choose_datatype').'.</p>';

		/*
			Put the result set (list of forums for this group) into a column with folders
		*/
		$tablearr=array($Language->getText('group','short_tracker'),$Language->getText('tracker_admin_update_type','description'));
		echo $HTML->listTableTop($tablearr);

		for ($j = 0; $j < count($at_arr); $j++) {
			echo '
			<tr '. $HTML->boxGetAltRowStyle($j) . '>
				<td><a href="/tracker/admin/?atid='. $at_arr[$j]->getID() . '&amp;group_id='.$group_id.'">' .
					html_image("ic/tracker20w.png","20","20",array("border"=>"0")) . ' &nbsp;'.
					$at_arr[$j]->getName() .'</a>
				</td>
				<td>'.$at_arr[$j]->getDescription() .'
				</td>
			</tr>';
		}
		echo $HTML->listTableBottom();
	}

	//
	//	Set up blank ArtifactType
	//

	if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
		//show nothing
	} else {

	?><?php echo $Language->getText('tracker_admin','intro') ?>
	<p>
	<form action="<?php echo $PHP_SELF.'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="add_at" value="y" />
	<p>
	<?php echo $Language->getText('tracker_admin_update_type','name') ?><br />
	<input type="text" name="name" value=""></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','description') ?>:</strong><br />
	<input type="text" name="description" value="" size="50" /></p>
	<p>
	<input type="checkbox" name="is_public" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','publicy_available') ?></strong><br />
	<input type="checkbox" name="allow_anon" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','allow_anonymous') ?></strong><br />
	<input type="checkbox" name="use_resolution" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','display_resolution') ?></strong></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','send_submissions') ?>:</strong><br />
	<input type="text" name="email_address" value="" /></p>
	<p>
	<input type="checkbox" name="email_all" value="1" /> <strong><?php echo $Language->getText('tracker_admin_update_type','email_all_changes') ?></strong><br /></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','days_overdue') ?>:</strong><br />
	<input type="text" name="due_period" value="30" /></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','pending_timeout') ?>:</strong><br />
	<input type="text" name="status_timeout" value="14" /></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','submit_item_form_text') ?>:</strong><br />
	<textarea name="submit_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<strong><?php echo $Language->getText('tracker_admin_update_type','browse_item_form_text') ?>:</strong><br />
	<textarea name="browse_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<input type="submit" name="post_changes" value="<?php echo $Language->getText('general','submit') ?>" /></p>
	</form></p>
	<?php
	}

	echo site_project_footer(array());

?>
