<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

// require("exit.php");

function html_feedback_top($feedback) {
	if (!$feedback) 
		return '';
	print '
		<H3><FONT COLOR="RED">'.$feedback.'</FONT></H3>';
}

function html_feedback_bottom($feedback) {
	if (!$feedback) 
		return '';
	print '
		<H3><FONT COLOR="RED">'.$feedback.'</FONT></H3>';
}

function html_a_group($grp) {
	print '<A /project/?group_id='.$grp.'>' . group_getname($grp) . '</A>';
}

function html_blankimage($height,$width) {
	return '<img src="/images/blank.gif" width="' . $width . '" height="' . $height . '" alt="">';
}

function html_dbimage($id) {
	if (!$id) {
		return '';
	}
	$sql="SELECT width,height ".
		"FROM db_images WHERE id='$id'";
	$result=db_query($sql);
	$rows=db_numrows($result);
	
	if (!$result || $rows < 1) {
		return db_error();
	} else {
		return html_image('/dbimage.php?id='.$id,db_result($result,0,'width'),db_result($result,0,'height'),array());
	}
}

function html_image($src,$width,$height,$args,$display=1) {
	global $sys_images_url;
	$return = ('<IMG src="' . $sys_images_url . $src .'"');
	reset($args);
	while(list($k,$v) = each($args)) {
		$return .= ' '.$k.'="'.$v.'"';
	}

	// ## insert a border tag if there isn't one
	if (!$args['border']) $return .= (" border=0");

	// ## add image dimensions
	$return .= " width=" . $width;
	$return .= " height=" . $height;

	$return .= ('>');
	return $return;
}



/*

	Pop up box of supported languages

	requires
	BaseLanguage object
	Title
	Selected
*/

function html_get_language_popup ($Language,$title='language_id',$selected='xzxzxz') {
	$res=$Language->getLanguages();
	return html_build_select_box ($res,$title,$selected,false);
}



/*

	Pop up box of supported Timezones

	Assumes you have included Timezones array file

	requires

	title,
	selected

*/

function html_get_timezone_popup ($title='timezone',$selected='xzxzxzx') {
	global $TZs;
	if ($selected == 'xzxzxzx') {
	  $r = file ('/etc/timezone');
	  $selected = str_replace ("\n", '', $r[0]);
	}
	return html_build_select_box_from_arrays ($TZs,$TZs,$title,$selected,false);
}

function html_build_list_table_top ($title_arr,$links_arr=false) {
	/*
		Takes an array of titles and builds
		The first row of a new table

		Optionally takes a second array of links for the titles
	*/
	GLOBAL $HTML;

	$return = '
	<TABLE WIDTH="100%" BORDER="0" CELLSPACING="1" CELLPADDING="2">
		<TR BGCOLOR="'. $HTML->COLOR_HTMLBOX_TITLE .'">';

	$count=count($title_arr);
	if ($links_arr) {
		for ($i=0; $i<$count; $i++) {
			$return .= '
			<TD ALIGN="MIDDLE"><a class=sortbutton href="'.$links_arr[$i].'"><FONT COLOR="'.
			$HTML->FONTCOLOR_HTMLBOX_TITLE.'"><B>'.$title_arr[$i].'</B></FONT></A></TD>';
		}
	} else {
		for ($i=0; $i<$count; $i++) {
			$return .= '
			<TD ALIGN="MIDDLE"><FONT COLOR="'.
			$HTML->FONTCOLOR_HTMLBOX_TITLE.'"><B>'.$title_arr[$i].'</B></FONT></TD>';
		}
	}
	return $return.'</TR>';
}

function html_get_alt_row_color ($i) {
	GLOBAL $HTML;
	if ($i % 2 == 0) {
		return '#FFFFFF';
	} else {
		return $HTML->COLOR_LTBACK1;
	}
}

function html_build_select_box_from_array ($vals,$select_name,$checked_val='xzxz',$samevals = 0) {
	/*
		Takes one array, with the first array being the "id" or value
		and the array being the text you want displayed

		The second parameter is the name you want assigned to this form element

		The third parameter is optional. Pass the value of the item that should be checked
	*/

	$return .= '
		<SELECT NAME="'.$select_name.'">';

	$rows=count($vals);

	for ($i=0; $i<$rows; $i++) {
		if ( $samevals ) {
			$return .= "\n\t\t<OPTION VALUE=\"" . $vals[$i] . "\"";
			if ($vals[$i] == $checked_val) {
				$return .= ' SELECTED';
			}
		} else {
			$return .= "\n\t\t<OPTION VALUE=\"" . $i .'"';
			if ($i == $checked_val) {
				$return .= ' SELECTED';
			}
		}
		$return .= '>'.$vals[$i].'</OPTION>';
	}
	$return .= '
		</SELECT>';

	return $return;
}

function html_build_select_box_from_arrays ($vals,$texts,$select_name,$checked_val='xzxz',$show_100=true,$text_100='None') {
	/*

		The infamous '100 row' has to do with the
			SQL Table joins done throughout all this code.
		There must be a related row in users, categories, et	, and by default that
			row is 100, so almost every pop-up box has 100 as the default
		Most tables in the database should therefore have a row with an id of 100 in it
			so that joins are successful

		Params:

		Takes two arrays, with the first array being the "id" or value
		and the other array being the text you want displayed

		The third parameter is the name you want assigned to this form element

		The fourth parameter is optional. Pass the value of the item that should be checked

		The fifth parameter is an optional boolean - whether or not to show the '100 row'

		The sixth parameter is optional - what to call the '100 row' defaults to none
	*/

	$return .= '
		<SELECT NAME="'.$select_name.'">';

	//we don't always want the default 100 row shown
	if ($show_100) {
		$return .= '
		<OPTION VALUE="100">'. $text_100 .'</OPTION>';
	}

	$rows=count($vals);
	if (count($texts) != $rows) {
		$return .= 'ERROR - uneven row counts';
	}

	for ($i=0; $i<$rows; $i++) {
		//  uggh - sorry - don't show the 100 row
		//  if it was shown above, otherwise do show it
		if (($vals[$i] != '100') || ($vals[$i] == '100' && !$show_100)) {
			$return .= '
				<OPTION VALUE="'.$vals[$i].'"';
			if ($vals[$i] == $checked_val) {
				$return .= ' SELECTED';
			}
			$return .= '>'.$texts[$i].'</OPTION>';
		}
	}
	$return .= '
		</SELECT>';
	return $return;
}

function html_build_select_box ($result, $name, $checked_val="xzxz",$show_100=true,$text_100='None') {
	/*
		Takes a result set, with the first column being the "id" or value
		and the second column being the text you want displayed

		The second parameter is the name you want assigned to this form element

		The third parameter is optional. Pass the value of the item that should be checked

		The fourth parameter is an optional boolean - whether or not to show the '100 row'

		The fifth parameter is optional - what to call the '100 row' defaults to none
	*/

	return html_build_select_box_from_arrays (util_result_column_to_array($result,0),util_result_column_to_array($result,1),$name,$checked_val,$show_100,$text_100);
}

function html_build_multiple_select_box ($result,$name,$checked_array,$size='8') {
	/*
		Takes a result set, with the first column being the "id" or value
		and the second column being the text you want displayed

		The second parameter is the name you want assigned to this form element

		The third parameter is an array of checked values;

		The fourth parameter is optional. Pass the size of this box
	*/

	$checked_count=count($checked_array);
//      echo '-- '.$checked_count.' --';
	$return .= '
		<SELECT NAME="'.$name.'" MULTIPLE SIZE="'.$size.'">';
	/*
		Put in the default NONE box
	*/
	$return .= '
		<OPTION VALUE="100"';
	for ($j=0; $j<$checked_count; $j++) {
		if ($checked_array[$j] == '100') {
			$return .= ' SELECTED';
		}
	}
	$return .= '>None</OPTION>';

	$rows=db_numrows($result);

	for ($i=0; $i<$rows; $i++) {
		if (db_result($result,$i,0) != '100') {
			$return .= '
				<OPTION VALUE="'.db_result($result,$i,0).'"';
			/*
				Determine if it's checked
			*/
			$val=db_result($result,$i,0);
			for ($j=0; $j<$checked_count; $j++) {
				if ($val == $checked_array[$j]) {
					$return .= ' SELECTED';
				}
			}
			$return .= '>'.$val.'-'. substr(db_result($result,$i,1),0,35). '</OPTION>';
		}
	}
	$return .= '
		</SELECT>';
	return $return;
}

function html_buildpriority_select_box ($name='priority', $checked_val='5') {
	/*
		Return a select box of standard priorities.
		The name of this select box is optional and so is the default checked value
	*/
	?>
	<SELECT NAME="<?php echo $name; ?>">
	<OPTION VALUE="1"<?php if ($checked_val=="1") {echo " SELECTED";} ?>>1 - Lowest</OPTION>
	<OPTION VALUE="2"<?php if ($checked_val=="2") {echo " SELECTED";} ?>>2</OPTION>
	<OPTION VALUE="3"<?php if ($checked_val=="3") {echo " SELECTED";} ?>>3</OPTION>
	<OPTION VALUE="4"<?php if ($checked_val=="4") {echo " SELECTED";} ?>>4</OPTION>
	<OPTION VALUE="5"<?php if ($checked_val=="5") {echo " SELECTED";} ?>>5 - Medium</OPTION>
	<OPTION VALUE="6"<?php if ($checked_val=="6") {echo " SELECTED";} ?>>6</OPTION>
	<OPTION VALUE="7"<?php if ($checked_val=="7") {echo " SELECTED";} ?>>7</OPTION>
	<OPTION VALUE="8"<?php if ($checked_val=="8") {echo " SELECTED";} ?>>8</OPTION>
	<OPTION VALUE="9"<?php if ($checked_val=="9") {echo " SELECTED";} ?>>9 - Highest</OPTION>
	</SELECT>
<?php

}

function html_buildcheckboxarray($options,$name,$checked_array) {
	$option_count=count($options);
	$checked_count=count($checked_array);

	for ($i=1; $i<=$option_count; $i++) {
		echo '
			<BR><INPUT type="checkbox" name="'.$name.'" value="'.$i.'"';
		for ($j=0; $j<$checked_count; $j++) {
			if ($i == $checked_array[$j]) {
				echo ' CHECKED';
			}
		}
		echo '> '.$options[$i];
	}
}

/*!     @function site_user_header
	@abstract everything required to handle security and
		add navigation for user pages like /my/ and /account/
	@param params array() must contain $user_id
	@result text - echos HTML to the screen directly
*/
function site_header($params) {							 GLOBAL $HTML;
	GLOBAL $HTML;
	/*
		Check to see if active user
		Check to see if logged in
	*/
	echo $HTML->header($params);
	echo html_feedback_top($GLOBALS['feedback']);
}

function site_footer($params) {
	GLOBAL $HTML;
	$HTML->footer($params);
}

/*! 	@function site_project_header
	@abstract everything required to handle security and state checks for a project web page
	@param params array() must contain $toptab and $group
	@result text - echos HTML to the screen directly
*/
function site_project_header($params) {
	GLOBAL $HTML;

	/*
		Check to see if active
		Check to see if project rather than foundry
		Check to see if private (if private check if user_ismember)
	*/

	$group_id=$params['group'];

	//get the project object 
	$project=&project_get_object($group_id);

	if (!$project || $project->isError()) {
		exit_error("Group Problem",$project->getErrorMessage());
	}

	//group is private
	if (!$project->isPublic()) {
		//if it's a private group, you must be a member of that group
		session_require(array('group'=>$group_id));
	}

	//for dead projects must be member of admin project
	if (!$project->isActive()) {
		//only SF group can view non-active, non-holding groups
		session_require(array('group'=>'1'));
	}

	echo $HTML->header($params);
	echo html_feedback_top($GLOBALS['feedback']);
	echo $HTML->project_tabs($params['toptab'],$params['group']);
}

/*!     @function site_project_footer
	@abstract currently a simple shim that should be on every project page, 
		rather than a direct call to site_footer() or theme_footer()
	@param params array() empty
	@result text - echos HTML to the screen directly
*/
function site_project_footer($params) {
	GLOBAL $HTML;

	echo html_feedback_bottom($GLOBALS['feedback']);
	echo $HTML->footer($params);
}

/*!     @function site_user_header
	@abstract everything required to handle security and 
		add navigation for user pages like /my/ and /account/
	@param params array() must contain $user_id
	@result text - echos HTML to the screen directly
*/
function site_user_header($params) {
	GLOBAL $HTML;

	/*
		Check to see if active user
		Check to see if logged in
	*/
	echo $HTML->header($params);
	echo html_feedback_top($GLOBALS['feedback']);
	echo '
	<P>
	<A HREF="/my/">My Personal Page</A> | <A HREF="/my/diary.php">Diary &amp; Notes</A> | <A HREF="/account/">Account Options</A>
	<P>';

}

/*!     @function site_user_footer
	@abstract currently a simple shim that should be on every user page, 
		rather than a direct call to site_footer() or theme_footer()
	@param params array() empty
	@result text - echos HTML to the screen directly
*/
function site_user_footer($params) {
	GLOBAL $HTML;

	echo html_feedback_bottom($GLOBALS['feedback']);
	echo $HTML->footer($params);
}       

?>
