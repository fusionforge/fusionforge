<?php
//
//  get the Group object
//
$group =& group_get_object($group_id);
if (!$group || !is_object($group) || $group->isError()) {
	exit_no_group();
}

$atf = new ArtifactTypeFactory($group);
if (!$group || !is_object($group) || $group->isError()) {
	exit_error('Error','Could Not Get ArtifactTypeFactory');
}

$at_arr =& $atf->getArtifactTypes();

//required params for site_project_header();
$params['group']=$group_id;
$params['toptab']='tracker';

echo site_project_header($params);

if (!$at_arr || count($at_arr) < 1) {
	echo "<h1>".$Language->getText('tracker','no_trackers')."</h1>";
	echo "<p><strong>".$Language->getText('tracker','no_trackers_text',array('<a href="'.$GLOBALS['sys_urlprefix'].'/tracker/admin/?group_id='.$group_id.'">','</a>'))."</strong>";
	} else {

	echo '<p>'.$Language->getText('tracker', 'choose').'<p>';

	/*
		Put the result set (list of trackers for this group) into a column with folders
	*/
	$tablearr=array($Language->getText('group','short_tracker'),$Language->getText('tracker_admin_update_type','description'),$Language->getText('general','open'),$Language->getText('general','total'));
	echo $HTML->listTableTop($tablearr);

	for ($j = 0; $j < count($at_arr); $j++) {
		if (!is_object($at_arr[$j])) {
			//just skip it
		} elseif ($at_arr[$j]->isError()) {
			echo $at_arr[$j]->getErrorMessage();
		} else {
		echo '
		<tr '. $HTML->boxGetAltRowStyle($j) . '>
			<td><a href="'.$GLOBALS['sys_urlprefix'].'/tracker/?atid='.$at_arr[$j]->getID().'&amp;group_id='.$group_id.'&func=browse">'.
				html_image("ic/tracker20w.png","20","20",array("border"=>"0")).' &nbsp;'.
				$at_arr[$j]->getName() .'</a>
			</td>
			<td>' .  $at_arr[$j]->getDescription() .'
			</td>
			<td style="text-align:center">'. (int) $at_arr[$j]->getOpenCount() . '
			</td>
			<td style="text-align:center">'. (int) $at_arr[$j]->getTotalCount() .'
			</td>
		</tr>';
		}
	}
	echo $HTML->listTableBottom();
}

echo site_project_footer(array());

?>
