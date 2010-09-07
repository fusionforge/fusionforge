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
$params['title']=sprintf(_('Trackers for %1$s'), $group->getPublicName());
$params['toptab']='tracker';

echo site_project_header($params);

if (forge_check_perm ('tracker_admin', $group_id)) {
	$menu_text=array();
	$menu_links=array();
	$menu_text[]=_('Admin');
	$menu_links[]='/tracker/admin/?group_id='.$group_id;
	echo $HTML->subMenu($menu_text,$menu_links);
}


if (!$at_arr || count($at_arr) < 1) {
	echo '<div class="error">'._('No Accessible Trackers Found').'</div>';
	echo "<p><strong>".sprintf(_('No trackers have been set up, or you cannot view them.<p><span class="important">The Admin for this project will have to set up data types using the %1$s admin page %2$s</span>'), '<a href="'.util_make_url ('/tracker/admin/?group_id='.$group_id).'">', '</a>')."</strong>";
} else {

	plugin_hook ("blocks", "tracker index");

	echo '<p>'._('Choose a tracker and you can browse/edit/add items to it.').'</p>';

	/*
		Put the result set (list of trackers for this group) into a column with folders
	*/
	$tablearr=array(_('Tracker'),_('Description'),_('Open'),_('Total'));
	echo $HTML->listTableTop($tablearr);

	for ($j = 0; $j < count($at_arr); $j++) {
		if (!is_object($at_arr[$j])) {
			//just skip it
		} elseif ($at_arr[$j]->isError()) {
			echo $at_arr[$j]->getErrorMessage();
		} else {
			echo '
		<tr '. $HTML->boxGetAltRowStyle($j) . '>
			<td><a href="'.util_make_url ('/tracker/?atid='.$at_arr[$j]->getID().'&amp;group_id='.$group_id.'&amp;func=browse').'">'.
 				html_image("ic/tracker20w.png","20","20").' &nbsp;'.
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
