<?php
/**
 * Misc HTML functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * html_feedback_top() - Show the feedback output at the top of the page.
 *
 * @param		string	The feedback.
 */
function html_feedback_top($feedback) {
	if (!$feedback) 
		return '';
	print '
		<H3><FONT COLOR="RED">'.$feedback.'</FONT></H3>';
}

/**
 * make_user_link() - Make a username reference into a link to that users User page on SF.
 *
 * @param		string	The username of the user to link.
 */
function make_user_link($username) {
	if (!strcasecmp($username,'Nobody') || !strcasecmp($username,'None')) {
		return $username;
	} else {
		return '<a href="/users/'.$username.'">'.$username.'</a>' ;
	}
}

/**
 * html_feedback_top() - Show the feedback output at the bottom of the page.
 *
 * @param		string	The feedback.
 */
function html_feedback_bottom($feedback) {
	if (!$feedback) 
		return '';
	print '
		<H3><FONT COLOR="RED">'.$feedback.'</FONT></H3>';
}

/**
 * html_a_group() - Turn a group name into a link to that groups summary page.
 *
 * @param		string	The group name.
 */
function html_a_group($grp) {
	print '<A /project/?group_id='.$grp.'>' . group_getname($grp) . '</A>';
}

/**
 * html_blankimage() - Show the blank spacer image.
 *
 * @param		int		The height of the image
 * @param		int		The width of the image
 */
function html_blankimage($height,$width) {
	return '<img src="/images/blank.png" width="' . $width . '" height="' . $height . '" alt="">';
}

/**
 * html_dbimage() - Show an image that is stored in the database
 *
 * @param		int		The id of the image to show
 */
function html_dbimage($id, $args=0) {
	if (!$id) {
		return '';
	}
	if (!$args) {
		$args = array();
	}
	$sql="SELECT width,height,version ".
		"FROM db_images WHERE id='$id'";
	$result=db_query($sql);
	$rows=db_numrows($result);
	
	if (!$result || $rows < 1) {
		return db_error();
	} else {
		return html_image('/dbimage.php?id='.$id.'&v='.db_result($result,0,'version'),db_result($result,0,'width'),db_result($result,0,'height'),$args);
	}
}

/**
 * html_image() - Build an image tag of an image contained in $src
 *
 * @param		string	The source location of the image
 * @param		int		The width of the image
 * @param		int		The height of the image
 * @param		array	Any IMG tag parameters associated with this image (i.e. 'border', 'alt', etc...)
 * @param		bool	DEPRECATED
 */
function html_image($src,$width,$height,$args,$display=1) {
	global $sys_images_url;
	$s = ((session_issecure()) ? 's' : '' );
	$return = ('<IMG src="http'. $s .':' . $sys_images_url . $src .'"');
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

/**
 * url_image() - Build an image url of an image contained in $src
 *
 * @param		string	The source location of the image
 */
function url_image($src) {
	global $sys_images_url;
	$s = ((session_issecure()) ? 's' : '' );
	return ('"http'. $s .':' . $sys_images_url . $src .'"');
}

/**
 * html_get_language_popup() - Pop up box of supported languages
 *
 * @param		object	BaseLanguage object
 * @param		string	The title of the popup box
 * @param		string	Which element of the box is to be selected
 */
function html_get_language_popup ($Language,$title='language_id',$selected='xzxzxz') {
	$res=$Language->getLanguages();
	return html_build_select_box ($res,$title,$selected,false);
}

/**
 * html_get_timezone_popup() - Pop up box of supported Timezones
 * Assumes you have included Timezones array file
 *
 * @param		string	The title of the popup box
 * @param		string	Which element of the box is to be selected
 */
function html_get_timezone_popup ($title='timezone',$selected='xzxz') {
	global $TZs;
	if ($selected == 'xzxzxzx') {
	  $r = file ('/etc/timezone');
	  $selected = str_replace ("\n", '', $r[0]);
	}
	return html_build_select_box_from_arrays ($TZs,$TZs,$title,$selected,false);
}

/**
 * html_build_list_table_top() - Takes an array of titles and builds the first row of a new table.
 *
 * @param		array	The array of titles
 * @param		array	The array of title links
 */
function html_build_list_table_top ($title_arr,$links_arr=false) {
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

/**
 * html_get_alt_row_color() - Get an alternating row color for tables.
 *
 * @param		int		Row number
 */
function html_get_alt_row_color ($i) {
	GLOBAL $HTML;
	if ($i % 2 == 0) {
		return '#FFFFFF';
	} else {
		return $HTML->COLOR_LTBACK1;
	}
}

/**
 * html_build_select_box_from_array() - Takes one array, with the first array being the "id"
 * or value and the array being the text you want displayed.
 *
 * @param		string	The name you want assigned to this form element
 * @param		string	The value of the item that should be checked
 */
function html_build_select_box_from_array ($vals,$select_name,$checked_val='xzxz',$samevals = 0) {
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

/**
 * html_build_select_box_from_arrays() - Takes two arrays, with the first array being the "id" or value and the other
 * array being the text you want displayed.
 *
 * The infamous '100 row' has to do with the SQL Table joins done throughout all this code.
 * There must be a related row in users, categories, et	, and by default that
 * row is 100, so almost every pop-up box has 100 as the default
 * Most tables in the database should therefore have a row with an id of 100 in it so that joins are successful
 *
 * @param		array	The ID or value
 * @param		array	Text to be displayed
 * @param		string	Name to assign to this form element
 * @param		string	The item that should be checked
 * @param		bool	Whether or not to show the '100 row'
 * @param		string	What to call the '100 row' defaults to none
 */
function html_build_select_box_from_arrays ($vals,$texts,$select_name,$checked_val='xzxz',$show_100=true,$text_100='None') {
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
				$checked_found=true;
				$return .= ' SELECTED';
			}
			$return .= '>'.$texts[$i].'</OPTION>';
		}
	}
	//
	//	If the passed in "checked value" was never "SELECTED"
	//	we want to preserve that value UNLESS that value was 'xzxz', the default value
	//
	if (!$checked_found && $checked_val != 'xzxz' && $checked_val && $checked_val != 100) {
		$return .= '
		<OPTION VALUE="'.$checked_val.'" SELECTED>No Change</OPTION>';
	}
	$return .= '
		</SELECT>';
	return $return;
}

/**
 * html_build_select_box() - Takes a result set, with the first column being the "id" or value and
 * the second column being the text you want displayed.
 *
 * @param		int		The result set
 * @param		string	Text to be displayed
 * @param		string	The item that should be checked
 * @param		bool	Whether or not to show the '100 row'
 * @param		string	What to call the '100 row'.  Defaults to none.
 */
function html_build_select_box ($result, $name, $checked_val="xzxz",$show_100=true,$text_100='None') {
	return html_build_select_box_from_arrays (util_result_column_to_array($result,0),util_result_column_to_array($result,1),$name,$checked_val,$show_100,$text_100);
}
/**
 * html_build_multiple_select_box() - Takes a result set, with the first column being the "id" or value
 * and the second column being the text you want displayed.
 *
 * @param		int		The result set
 * @param		string	Text to be displayed
 * @param		string	The item that should be checked
 * @param		int		The size of this box
 * @param		bool	Whether or not to show the '100 row'
 */
function html_build_multiple_select_box ($result,$name,$checked_array,$size='8',$show_100=true) {
	$checked_count=count($checked_array);
	$return .= '
		<SELECT NAME="'.$name.'" MULTIPLE SIZE="'.$size.'">';
	if ($show_100) {
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
	}

	$rows=db_numrows($result);

	for ($i=0; $i<$rows; $i++) {
		if ((db_result($result,$i,0) != '100') || (db_result($result,$i,0) == '100' && !$show_100)) {
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

/**
 *	html_build_checkbox() - Render checkbox control
 *
 *	@param name - name of control
 *	@param value - value of control
 *	@param checked - true if control should be checked
 *	@return html code for checkbox control
 */
function html_build_checkbox($name, $value, $checked) {
	return '<input type="checkbox" name="'.$name.'"'
		.' value="'.$value.'"'
		.($checked ? 'checked' : '').'>';
}

/**
 * get_priority_color() - Wrapper for html_get_priority_color().
 * 
 * @see	html_get_priority_color()
 */
function get_priority_color ($index) {
	return html_get_priority_color ($index);
}

/**
 * html_get_priority_color() - Return the color value for the index that was passed in
 * (defined in $sys_urlroot/themes/<selected theme>/theme.php)
 *
 * @param		int		Index
 */
function html_get_priority_color ($index) {
	/* make sure that index is of appropriate type and range */
	$index = (int)$index;
	if ($index<1) {
		$index=1;
	} else if ($index>9) {
		$index=9;
	}   
	return "prior$index";
}

/**
 * build_priority_select_box() - Wrapper for html_build_priority_select_box()
 *
 * @see html_build_priority_select_box()
 */
function build_priority_select_box ($name='priority', $checked_val='5', $nochange=false) {
	echo html_build_priority_select_box ($name, $checked_val, $nochange);
}

/**
 * html_build_priority_select_box() - Return a select box of standard priorities.
 * The name of this select box is optional and so is the default checked value.
 *
 * @param		string	Name of the select box
 * @param		string	The value to be checked
 * @param		bool	Whether to make 'No Change' selected.
 */
function html_build_priority_select_box ($name='priority', $checked_val='5', $nochange=false) {
?>
	<SELECT NAME="<?php echo $name; ?>">
<?php if($nochange) { ?>
	<OPTION VALUE="100"<?php if ($nochange) {echo " SELECTED";} ?>>No Change</OPTION>
<?php }  ?>
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

/**
 * html_buildcheckboxarray() - Build an HTML checkbox array.
 *
 * @param		array	Options array
 * @param		name	Checkbox name
 * @param		array	Array of boxes to be pre-checked
 */
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

/** 
 *	site_user_header() - everything required to handle security and
 *	add navigation for user pages like /my/ and /account/
 *
 *	@param		array	Must contain $user_id
 */
function site_header($params) {
	GLOBAL $HTML;
	/*
		Check to see if active user
		Check to see if logged in
	*/
	echo $HTML->header($params);
	echo html_feedback_top($GLOBALS['feedback']);
}

/**
 * site_footer() - Show the HTML site footer.
 *
 * @param		array	Footer params array
 */
function site_footer($params) {
	GLOBAL $HTML;
	$HTML->footer($params);
}

/**
 *	site_project_header() - everything required to handle 
 *	security and state checks for a project web page
 *
 *	@param params array() must contain $toptab and $group
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
	$project =& group_get_object($group_id);

	if (!$project || !is_object($project)) {
		exit_error("GROUP PROBLEM","PROBLEM CREATING GROUP OBJECT");
	} else if ($project->isError()) {
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
	echo $HTML->project_tabs($params['toptab'],$params['group'],$params['tabtext']);
}

/**
 *	site_project_footer() - currently a simple shim 
 *	that should be on every project page,  rather than 
 *	a direct call to site_footer() or theme_footer()
 *
 *	@param params array() empty
 */
function site_project_footer($params) {
	GLOBAL $HTML;

	echo html_feedback_bottom($GLOBALS['feedback']);
	echo $HTML->footer($params);
}

/**
 *	site_user_header() - everything required to handle security and 
 *	add navigation for user pages like /my/ and /account/
 *
 *	@param params array() must contain $user_id
 */
function site_user_header($params) {
	GLOBAL $HTML;

	/*
		Check to see if active user
		Check to see if logged in
	*/
	echo $HTML->header($params);
	echo html_feedback_top($GLOBALS['feedback']);
	echo $HTML->user_menu($params);
}

/** 
 *	site_user_footer() - currently a simple shim that should be on every user page, 
 *	rather than a direct call to site_footer() or theme_footer()
 *
 *	@param params array() empty
 */
function site_user_footer($params) {
	GLOBAL $HTML;

	echo html_feedback_bottom($GLOBALS['feedback']);
	echo $HTML->footer($params);
}	   

/** 
 *	html_clean_hash_string() - Remove noise characters from hex hash string
 *	
 *	Thruout SourceForge, URLs with hexadecimal hash string parameters
 *	are being sent via email to request confirmation of user actions. 
 *	It was found that some mail clients distort this hash, so we take
 *	special steps to encode it in the way which help to preserve its
 *	recognition. This routine 
 *
 *	@param hashstr required hash parameter as received from browser
 *	@return pure hex string
 */
function html_clean_hash_string($hashstr) {

        if (substr($hashstr,0,1)=="_") {
                $hashstr = substr($hashstr, 1);
        }
				
        if (substr($hashstr, strlen($hashstr)-1, 1)==">") {
                $hashstr = substr($hashstr, 0, strlen($hashstr)-1);
        }
	
	return $hashstr;
}

?>
