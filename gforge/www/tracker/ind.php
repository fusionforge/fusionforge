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
	echo "<h1>"._('No Accessible Trackers Found')."</h1>";
	echo "<p><strong>".sprintf(_('No trackers have been set up, or you cannot view them.<p><span class="important">The Admin for this project will have to set up data types using the %1$s admin page %2$s</span>'), '<a href="'.$GLOBALS['sys_urlprefix'].'/tracker/admin/?group_id='.$group_id.'">', '</a>')."</strong>";
	} else {

	echo '<p>'._('Choose a tracker and you can browse/edit/add items to it.').'<p>';

	/*
		Put the result set (list of trackers for this group) into a column with folders
	*/
	$tablearr=array(_('Tracker'),_('Tracker'),_('Tracker'),_('Tracker'));
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
