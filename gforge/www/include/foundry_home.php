<?php

require($DOCUMENT_ROOT.'/news/news_utils.php');
require('features_boxes.php');
require('cache.php');

//we already know $foundry is set up from the master page

$HTML->header(array('title'=>$foundry->getUnixName().' - Foundry','group'=>$group_id));

echo'	<TABLE cellspacing="0" cellpadding="10" border="0" width="100%">
	      <TR>
		<TD align="left" valign="top" colspan="2">
';

echo html_dbimage($foundry->getLogoImageID());

echo '
		</td>
	      </tr>
	<TR>
	    <TD valign="top" align="left">
';

echo $foundry->getFreeformHTML1();

echo '
	&nbsp;<BR>
';

/*

	News that was selected for display by the portal
	News items are chosen froma list of news in subprojects

*/

$HTML->box1_top('Foundry News', '#f4f5f7');
echo news_foundry_latest($group_id);
$HTML->box1_bottom();

/*

	Message Forums

*/

echo '<P>
';

$HTML->box1_top('Discussion Forums');

//$sql="SELECT * FROM forum_group_list WHERE group_id='$group_id' AND is_public='1';";

$sql="SELECT g.group_forum_id,g.forum_name, g.description, count(*) as total " //, max(date) as latest"		 
	." FROM forum_group_list g, forum f"
	." WHERE g.group_id='$group_id' AND g.is_public=1"
	." AND g.group_forum_id = f.group_forum_id"
	." group by g.group_forum_id, g.forum_name, g.description";


$result = db_query ($sql);

$rows = db_numrows($result);

if (!$result || $rows < 1) {

	echo '<H1>No forums found for '. $foundry->getUnixName() .'</H1>';

} else {

	/*
		Put the result set (list of forums for this group) into a column with folders
	*/

	for ($j = 0; $j < $rows; $j++) {
		echo '
			<A HREF="/forum/forum.php?forum_id='. db_result($result, $j, 'group_forum_id') .'">'.
			html_image("images/ic/cfolder15.png","15","13",array("BORDER"=>"0")) . '&nbsp;'.
			db_result($result, $j, 'forum_name').'</A> ';
		//message count
		echo '('. db_result($result,$j,'total') .' msgs)';
		echo "<BR>\n";
		echo db_result($result,$j,'description').'<P>';
	}

}

$HTML->box1_bottom();

echo $foundry->getFreeformHTML2();

echo '</TD><TD VALIGN="TOP" WIDTH="30%">';

echo $foundry->getSponsorHTML1();

echo cache_display('foundry'.$group_id.'_features_boxes','foundry_features_boxes()',3600);

echo '</TD></TR></TABLE>';

$HTML->footer(array());

?>
