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
		<h3><span style="color:red">'.$feedback.'</span></h3>';
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
		<h3><span style="color:red">'.$feedback.'</span></h3>';
}

/**
 * html_blankimage() - Show the blank spacer image.
 *
 * @param		int		The height of the image
 * @param		int		The width of the image
 */
function html_blankimage($height,$width) {
	return '<img src="/images/blank.png" width="' . $width . '" height="' . $height . '" alt="" />';
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
		return html_abs_image('/dbimage.php?id='.$id.'&amp;v='.db_result($result,0,'version'),db_result($result,0,'width'),db_result($result,0,'height'),$args);
	}
}

/**
 * html_abs_image() - Show an image given an absolute URL.
 *
 * @param		string	URL
 * @param		int	width of the image
 * @param		int 	height of the image
 * @param		array	Any <img> tag parameters (i.e. 'border', 'alt', etc...)
 */
function html_abs_image($url, $width, $height, $args)
{
	$return = ('<img src="' . $url . '"');
	reset($args);
	while(list($k,$v) = each($args)) {
		$return .= ' '.$k.'="'.$v.'"';
	}

	// ## insert a border tag if there isn't one
	if (!isset($args['border'])) {
		$return .= ' border="0"';
	}

	if (!isset($args['alt'])) {
		$return .= ' alt=""';
	}

	// ## add image dimensions
	$return .= " width=\"" . $width . "\"";
	$return .= " height=\"" . $height . "\"";

	$return .= (' />');
	return $return;
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
	global $sys_images_url,$sys_images_secure_url,$HTML;
	$s = ((session_issecure()) ? $sys_images_secure_url : $sys_images_url );
	return html_abs_image($s.$HTML->imgroot.$src, $width, $height, $args);
}

/**
 * html_get_language_popup() - Pop up box of supported languages.
 *
 * @param		object	BaseLanguage object.
 * @param		string	The title of the popup box.
 * @param		string	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_language_popup ($Language,$title='language_id',$selected='xzxzxz') {
	$res=$Language->getLanguages();
	return html_build_select_box ($res,$title,$selected,false);
}

/**
 * html_get_ccode_popup() - Pop up box of supported country_codes.
 *
 * @param		string	The title of the popup box.
 * @param		string	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_ccode_popup ($title='ccode',$selected='xzxz') {
	$res=db_query("SELECT ccode,country_name FROM country_code ORDER BY ccode");
	return html_build_select_box ($res,$title,$selected,false);
}

/**
 * html_get_timezone_popup() - Pop up box of supported Timezones.
 * Assumes you have included Timezones array file.
 *
 * @param		string	The title of the popup box.
 * @param		string	Which element of the box is to be selected.
 * @return	string	The html select box.
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
 * html_build_select_box_from_assoc() - Takes one assoc array and returns a pop-up box.
 *
 * @param	array	An array of items to use.
 * @param	string	The name you want assigned to this form element.
 * @param	string	The value of the item that should be checked.
 * @param	boolean	Whether we should swap the keys / names.
 * @param	bool	Whether or not to show the '100 row'.
 * @param	string	What to call the '100 row' defaults to none.
 */
function html_build_select_box_from_assoc ($arr,$select_name,$checked_val='xzxz',$swap=false,$show_100=false,$text_100='None') {
	if ($swap) {
		$keys=array_values($arr);
		$vals=array_keys($arr);
	} else {
		$vals=array_values($arr);
		$keys=array_keys($arr);
	}
	return html_build_select_box_from_arrays ($keys,$vals,$select_name,$checked_val,$show_100,$text_100);
}

/**
 * html_build_select_box_from_array() - Takes one array, with the first array being the "id"
 * or value and the array being the text you want displayed.
 *
 * @param	array	An array of items to use.
 * @param	string	The name you want assigned to this form element.
 * @param	string	The value of the item that should be checked.
 */
function html_build_select_box_from_array ($vals,$select_name,$checked_val='xzxz',$samevals = 0) {
	$return .= '
		<select name="'.$select_name.'">';

	$rows=count($vals);

	for ($i=0; $i<$rows; $i++) {
		if ( $samevals ) {
			$return .= "\n\t\t<option value=\"" . $vals[$i] . "\"";
			if ($vals[$i] == $checked_val) {
				$return .= ' selected="selected"';
			}
		} else {
			$return .= "\n\t\t<option value=\"" . $i .'"';
			if ($i == $checked_val) {
				$return .= ' selected="selection"';
			}
		}
		$return .= '>'.htmlspecialchars($vals[$i]).'</option>';
	}
	$return .= '
		</select>';

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
function html_build_select_box_from_arrays ($vals,$texts,$select_name,$checked_val='xzxz',$show_100=true,$text_100='none') {
	global $Language;
	if ($text_100=='none'){
		$text_100=$Language->getText('include_html','none');
	}
	$return = '';
	
	$return .= '
		<select name="'.$select_name.'">';

	//we don't always want the default 100 row shown
	if ($show_100) {
		$return .= '
		<option value="100">'. $text_100 .'</option>';
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
				<option value="'.$vals[$i].'"';
			if ($vals[$i] == $checked_val) {
				$checked_found=true;
				$return .= ' selected="selected"';
			}
			$return .= '>'.htmlspecialchars($texts[$i]).'</option>';
		}
	}
	//
	//	If the passed in "checked value" was never "SELECTED"
	//	we want to preserve that value UNLESS that value was 'xzxz', the default value
	//
	if (!$checked_found && $checked_val != 'xzxz' && $checked_val && $checked_val != 100) {
		$return .= '
		<option value="'.$checked_val.'" selected="selected">'.$Language->getText('include_html','no_change').'</option>';
	}

	$return .= '
		</select>';
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
function html_build_select_box ($result, $name, $checked_val="xzxz",$show_100=true,$text_100='none') {
	global $Language;
	if ($text_100=='none'){
		$text_100=$Language->getText('include_html','none');
	}
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
	global $Language;
	$checked_count=count($checked_array);
	$return .= '
		<select name="'.$name.'" multiple="multiple" size="'.$size.'">';
	if ($show_100) {
		/*
			Put in the default NONE box
		*/
		$return .= '
		<option value="100"';
		for ($j=0; $j<$checked_count; $j++) {
			if ($checked_array[$j] == '100') {
				$return .= ' selected="selected"';
			}
		}
		$return .= '>'.$Language->getText('include_html','none').'</option>';
	}

	$rows=db_numrows($result);

	for ($i=0; $i<$rows; $i++) {
		if ((db_result($result,$i,0) != '100') || (db_result($result,$i,0) == '100' && !$show_100)) {
			$return .= '
				<option value="'.db_result($result,$i,0).'"';
			/*
				Determine if it's checked
			*/
			$val=db_result($result,$i,0);
			for ($j=0; $j<$checked_count; $j++) {
				if ($val == $checked_array[$j]) {
					$return .= ' selected="selected"';
				}
			}
			$return .= '>'.$val.'-'. substr(db_result($result,$i,1),0,35). '</option>';
		}
	}
	$return .= '
		</select>';
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
		.($checked ? 'checked="checked"' : '').'>';
}

/**
 * html_get_priority_color() - Return the color value for the index that was passed in
 * (defined in $sys_urlroot/themes/<selected theme>/theme.php)
 *
 * @param		int		Index
 */
function html_get_priority_color ($index) {
	global $bgpri;

	/* make sure that index is of appropriate type and range */
	$index = (int)$index;
	if ($index<1) {
		$index=1;
	} else if ($index>9) {
		$index=9;
	}
	//return "prior$index";
	return $bgpri[$index];
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
	global $Language;
?>
	<select name="<?php echo $name; ?>">
<?php if($nochange) { ?>
	<option value="100"<?php if ($nochange) {echo " selected=\"selected\"";} ?>><?php echo $Language->getText('include_html','no_change') ?></option>
<?php }  ?>
	<option value="1"<?php if ($checked_val=="1") {echo " selected=\"selected\"";} ?>>1 - <?php echo $Language->getText('include_html','priority_lowest') ?></option>
	<option value="2"<?php if ($checked_val=="2") {echo " selected=\"selected\"";} ?>>2</option>
	<option value="3"<?php if ($checked_val=="3") {echo " selected=\"selected\"";} ?>>3</option>
	<option value="4"<?php if ($checked_val=="4") {echo " selected=\"selected\"";} ?>>4</option>
	<option value="5"<?php if ($checked_val=="5") {echo " selected=\"selected\"";} ?>>5 - <?php echo $Language->getText('include_html','priority_medium') ?></option>
	<option value="6"<?php if ($checked_val=="6") {echo " selected=\"selected\"";} ?>>6</option>
	<option value="7"<?php if ($checked_val=="7") {echo " selected=\"selected\"";} ?>>7</option>
	<option value="8"<?php if ($checked_val=="8") {echo " selected=\"selected\"";} ?>>8</option>
	<option value="9"<?php if ($checked_val=="9") {echo " selected=\"selected\"";} ?>>9 - <?php echo $Language->getText('include_html','priority_highest') ?></option>
	</select>
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
			<br /><input type="checkbox" name="'.$name.'" value="'.$i.'"';
		for ($j=0; $j<$checked_count; $j++) {
			if ($i == $checked_array[$j]) {
				echo ' checked="checked"';
			}
		}
		echo ' /> '.$options[$i];
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
	
	if(isset($GLOBALS['feedback'])) {
		echo html_feedback_top($GLOBALS['feedback']);
	}
//	echo $HTML->project_tabs($params['toptab'],$params['group'],$params['tabtext']);
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

	if(isset($GLOBALS['feedback'])) {
		echo html_feedback_bottom($GLOBALS['feedback']);
	}
	echo $HTML->footer($params);
}

/**
 *	site_user_header() - everything required to handle security and
 *	add navigation for user pages like /my/ and /account/
 *
 *	@param params array() must contain $user_id
 */
function site_user_header($params) {
	GLOBAL $HTML,$Language;

	/*
		Check to see if active user
		Check to see if logged in
	*/
	echo $HTML->header($params);
	echo html_feedback_top((isset($GLOBALS['feedback']) ? $GLOBALS['feedback'] : ''));
	echo ($HTML->BeginSubMenu());
	echo ($HTML->PrintSubMenu(
		array($Language->getText('menu','my_personal_page'),
			$Language->getText('menu','diary_notes'),
			$Language->getText('menu','account_maintenance'),
			$Language->getText('menu','register_project')),
		array('"/my/"',
			'"/my/diary.php"',
			'"/account/"',
			'"/register/"')));
	plugin_hook ("usermenu", false) ;
	echo ($HTML->EndSubMenu());
}

/**
 *	site_user_footer() - currently a simple shim that should be on every user page,
 *	rather than a direct call to site_footer() or theme_footer()
 *
 *	@param params array() empty
 */
function site_user_footer($params) {
	GLOBAL $HTML;

	echo html_feedback_bottom((isset($GLOBALS['feedback']) ? $GLOBALS['feedback'] : ''));
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
