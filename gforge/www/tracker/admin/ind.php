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

	if (getStringFromRequest('post_changes')) {
		$name = getStringFromRequest('name');
		$description = getStringFromRequest('description');
		$is_public = getStringFromRequest('is_public');
		$allow_anon = getStringFromRequest('allow_anon');
		$email_all = getStringFromRequest('email_all');
		$email_address = getStringFromRequest('email_address');
		$due_period = getStringFromRequest('due_period');
		$use_resolution = getStringFromRequest('use_resolution');
		$submit_instructions = getStringFromRequest('submit_instructions');
		$browse_instructions = getStringFromRequest('browse_instructions');

		if (!$perm || !is_object($perm) || !$perm->isArtifactAdmin()) {
			exit_permission_denied();
		}

		if (getStringFromRequest('add_at')) {
			$res=new ArtifactTypeHtml($group);
			if (!$res->create($name,$description,$is_public,$allow_anon,$email_all,$email_address,
				$due_period,$use_resolution,$submit_instructions,$browse_instructions)) {
				$feedback .= $res->getErrorMessage();
			} else {
				header ("Location: ".$GLOBALS['sys_urlprefix']."/tracker/admin/?group_id=$group_id&atid=".$res->getID()."&update_users=1");
			}

		}
	}


	//
	//	Display existing artifact types
	//
	$atf = new ArtifactTypeFactory($group);
	if (!$atf || !is_object($atf) || $atf->isError()) {
		exit_error('Error','Could Not Get ArtifactTypeFactory');
	}

	$at_arr =& $atf->getArtifactTypes();

	//required params for site_project_header();
	$params['group']=$group_id;
	$params['toptab']='tracker';
	if(isset($page_title)){ 
		$params['title'] = $page_title;
	} else {
		$params['title'] = '';
	}

	echo site_project_header($params);
	echo $HTML->subMenu(
		array(
			_('Report'),
			_('Admin')
		),
		array(
			'/tracker/reporting/?group_id='.$group_id,
			'/tracker/admin/?group_id='.$group_id
		)
	);

	if (!$at_arr || count($at_arr) < 1) {
		echo "<h1>"._('No trackers found')."</h1>";
		echo "<p>&nbsp;</p>";
	} else {

		echo '
		<p>'._('Choose a data type and you can set up prefs, categories, groups, users, and permissions').'.</p>';

		/*
			Put the result set (list of forums for this group) into a column with folders
		*/
		$tablearr=array(_('Tracker'),_('Description'));
		echo $HTML->listTableTop($tablearr);

		for ($j = 0; $j < count($at_arr); $j++) {
			echo '
			<tr '. $HTML->boxGetAltRowStyle($j) . '>
				<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/admin/?atid='. $at_arr[$j]->getID() . '&amp;group_id='.$group_id.'">' .
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

	?><?php echo _('<h3>Create a new tracker</h3><p>You can use this system to track virtually any kind of data, with each tracker having separate user, group, category, and permission lists. You can also easily move items between trackers when needed.</p><p>Trackers are referred to as "Artifact Types" and individual pieces of data are "Artifacts". "Bugs" might be an Artifact Type, whiles a bug report would be an Artifact. You can create as many Artifact Types as you want, but remember you need to set up categories, groups, and permission for each type, which can get time-consuming') ?>
	<p>
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id; ?>" method="post">
	<input type="hidden" name="add_at" value="y" />
	<p>
	<?php echo _('<strong> Name:</strong> (examples: meeting minutes, test results, RFP Docs)') ?><br />
	<input type="text" name="name" value=""></p>
	<p>
	<strong><?php echo _('Description') ?>:</strong><br />
	<input type="text" name="description" value="" size="50" /></p>
	<p>
	<input type="checkbox" name="is_public" value="1" /> <strong><?php echo _('Publicly Available') ?></strong><br />
	<input type="checkbox" name="allow_anon" value="1" /> <strong><?php echo _('Allow non-logged-in postings') ?></strong><br />
	<p>
	<strong><?php echo _('Send email on new submission to address') ?>:</strong><br />
	<input type="text" name="email_address" value="" /></p>
	<p>
	<input type="checkbox" name="email_all" value="1" /> <strong><?php echo _('Send email on all changes') ?></strong><br /></p>
	<p>
	<strong><?php echo _('Days till considered overdue') ?>:</strong><br />
	<input type="text" name="due_period" value="30" /></p>
	<p>
	<strong><?php echo _('Days till pending tracker items time out') ?>:</strong><br />
	<input type="text" name="status_timeout" value="14" /></p>
	<p>
	<strong><?php echo _('Free form text for the "submit new item" page') ?>:</strong><br />
	<textarea name="submit_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<strong><?php echo _('Free form text for the "browse items" page') ?>:</strong><br />
	<textarea name="browse_instructions" rows="10" cols="55" wrap="hard"></textarea></p>
	<p>
	<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
	</form></p>
	<?php
	}

	echo site_project_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
