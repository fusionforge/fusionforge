<?php
/**
 * Misc HTML functions
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * Copyright 2010 (c) FusionForge Team
 * Copyright (C) 2010 Alain Peyrat - Alcatel-Lucent
 * Copyright 2011, Franck Villaume - Capgemini
 * Copyright 2011, Franck Villaume - TrivialDev
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * html_feedback_top() - Show the feedback output at the top of the page.
 *
 * @param	string	The feedback.
 */
function html_feedback_top($feedback) {
	global $HTML;
	echo $HTML->feedback($feedback);
}

/**
 * html_warning_top() - Show the warning output at the top of the page.
 *
 * @param	string	The warning message.
 */
function html_warning_top($msg) {
	global $HTML;
	echo $HTML->warning_msg($msg);
}

/**
 * html_error_top() - Show the error output at the top of the page.
 *
 * @param	string	The error message.
 */
function html_error_top($msg) {
	global $HTML;
	echo $HTML->error_msg($msg);
}

/**
 * make_user_link() - Make a username reference into a link to that users User page on SF.
 *
 * @param	string	The username of the user to link.
 */
function make_user_link($username,$displayname='') {
	if (empty($displayname))
		$displayname = $username;

	if (!strcasecmp($username,'Nobody') || !strcasecmp($username,'None')) {
		return $username;
	} else {
		return '<a href="/users/'.$username.'">'.$displayname.'</a>';
	}
}

/**
 * html_feedback_bottom() - Show the feedback output at the bottom of the page.
 *
 * @param	string	The feedback.
 */
function html_feedback_bottom($feedback) {
	global $HTML;
	echo $HTML->feedback($feedback);
}

/**
 * html_blankimage() - Show the blank spacer image.
 *
 * @param	int	The height of the image
 * @param	int	The width of the image
 */
function html_blankimage($height,$width) {
	return '<img src="/images/blank.png" width="' . $width . '" height="' . $height . '" alt="" />';
}

/**
 * html_abs_image() - Show an image given an absolute URL.
 *
 * @param	string	URL
 * @param	int	width of the image
 * @param	int 	height of the image
 * @param	array	Any <img> tag parameters (i.e. 'border', 'alt', etc...)
 */
function html_abs_image($url, $width, $height, $args) {
	$return = ('<img src="' . $url . '"');
	reset($args);
	while(list($k,$v) = each($args)) {
		$return .= ' '.$k.'="'.$v.'"';
	}

	if (!isset($args['alt'])) {
		$return .= ' alt=""';
	}

	// Add image dimensions (if given)
	$return .= $width ?" width=\"" . $width . "\"": '';
	$return .= $height? " height=\"" . $height . "\"": '';

	$return .= (' />');
	return $return;
}

/**
 * html_image() - Build an image tag of an image contained in $src
 *
 * @param	string	The source location of the image
 * @param	int	The width of the image
 * @param	int	The height of the image
 * @param	array	Any IMG tag parameters associated with this image (i.e. 'border', 'alt', etc...)
 * @param	bool	DEPRECATED
 */
function html_image($src, $width='', $height='', $args=array(), $display=1) {
	global $HTML;

	if (method_exists($HTML, 'html_image')) {
		$HTML->html_image($src, $width, $height, $args);
	}
	$s = ((session_issecure()) ? forge_get_config('images_secure_url') : forge_get_config('images_url') );
	return html_abs_image($s.$HTML->imgroot.$src, $width, $height, $args);
}

/**
 * html_get_language_popup() - Pop up box of supported languages.
 *
 * @param	string	The title of the popup box.
 * @param	string	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_language_popup($title='language_id', $selected='xzxz') {
	$res = db_query_params('SELECT * FROM supported_languages ORDER BY name ASC',
			array());
	return html_build_select_box($res, $title, $selected, false);
}

/**
 * html_get_theme_popup() - Pop up box of supported themes.
 *
 * @param	string	The title of the popup box.
 * @param	string	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_theme_popup($title='theme_id', $selected='xzxz') {
	$res=db_query_params('SELECT theme_id, fullname FROM themes WHERE enabled=true',
			array());
	$nbTheme = db_numrows($res);
	if($nbTheme < 2) {
		return("");
	}
	else {
		return html_build_select_box($res, $title, $selected, false);
	}
}

/**
 * html_get_ccode_popup() - Pop up box of supported country_codes.
 *
 * @param	string	The title of the popup box.
 * @param	string	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_ccode_popup($title='ccode', $selected='xzxz') {
	$res=db_query_params('SELECT ccode,country_name FROM country_code ORDER BY country_name',
			array());
	return html_build_select_box($res, $title, $selected, false);
}

/**
 * html_get_timezone_popup() - Pop up box of supported Timezones.
 * Assumes you have included Timezones array file.
 *
 * @param	string	The title of the popup box.
 * @param	string	Which element of the box is to be selected.
 * @return	string	The html select box.
 */
function html_get_timezone_popup($title='timezone', $selected='xzxz') {
	global $TZs;
	if ($selected == 'xzxzxzx') {
	  $r = file('/etc/timezone');
	  $selected = str_replace("\n", '', $r[0]);
	}
	return html_build_select_box_from_arrays($TZs, $TZs, $title, $selected, false);
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
	$return = '
		<select name="'.$select_name.'">';

	$rows = count($vals);

	for ($i = 0; $i < $rows; $i++) {
		if ( $samevals ) {
			$return .= "\n\t\t<option value=\"" . $vals[$i] . "\"";
			if ($vals[$i] == $checked_val) {
				$return .= ' selected="selected"';
			}
		} else {
			$return .= "\n\t\t<option value=\"" . $i .'"';
			if ($i == $checked_val) {
				$return .= ' selected="selected"';
			}
		}
		$return .= '>'.htmlspecialchars($vals[$i]).'</option>';
	}
	$return .= '
		</select>';

	return $return;
}

/**
 * html_build_radio_buttons_from_arrays() - Takes two arrays, with the first array being the "id" or value and the other
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
 * @param		bool	Whether or not to show the 'Any row'
 * @param		string	What to call the 'Any row' defaults to any
 */
function html_build_radio_buttons_from_arrays ($vals,$texts,$select_name,$checked_val='xzxz',$show_100=true,$text_100='none',$show_any=false,$text_any='any') {
	if ($text_100=='none'){
		$text_100=_('None');
	}
	$return = '';

	$rows=count($vals);
	if (count($texts) != $rows) {
		$return .= 'ERROR - uneven row counts';
	}

	//we don't always want the default Any row shown
	if ($show_any) {
		$return .= '
		<input type="radio" name="'.$select_name.'" value=""'.(($checked_val=='') ? ' checked="checked"' : '').' />&nbsp;'. $text_any .'<br />';
	}
	//we don't always want the default 100 row shown
	if ($show_100) {
		$return .= '
		<input type="radio" name="'.$select_name.'" value="100"'.(($checked_val==100) ? ' checked="checked"' : '').' />&nbsp;'. $text_100 .'<br />';
	}

	$checked_found=false;

	for ($i=0; $i<$rows; $i++) {
		//  uggh - sorry - don't show the 100 row
		//  if it was shown above, otherwise do show it
		if (($vals[$i] != '100') || ($vals[$i] == '100' && !$show_100)) {
			$return .= '
				<input type="radio" name="'.$select_name.'" value="'.$vals[$i].'"';
			if ((string)$vals[$i] == (string)$checked_val) {
				$checked_found=true;
				$return .= ' checked="checked"';
			}
			$return .= ' />&nbsp;'.htmlspecialchars($texts[$i]).'<br />';
		}
	}
	//
	//	If the passed in "checked value" was never "SELECTED"
	//	we want to preserve that value UNLESS that value was 'xzxz', the default value
	//
	if (!$checked_found && $checked_val != 'xzxz' && $checked_val && $checked_val != 100) {
		$return .= '
		<input type="radio" value="'.$checked_val.'" checked="checked" />&nbsp;'._('No Change').'<br />';
	}

	return $return;
}

function html_get_tooltip_description($element_name) {
	switch( $element_name ) {
		case 'assigned_to':
			return( _('This drop-down box represents the person to which a tracker item is assigned.'));
		case 'status_id':
			return( _('This drop-down box represents the current status of a tracker item.<br /><br />You can set the status to \'Pending\' if you are waiting for a response from the tracker item author.  When the author responds the status is automatically reset to that of \'Open\'. Otherwise, if the author doesn\'t respond with an admin-defined amount of time (default is 14 days) then the item is given a status of \'Deleted\'.'));
		case 'category':
			return( _('Tracker category'));
		case 'group':
			return(  _('Tracker group'));
		case 'sort_by':
			return( _('The Sort By option allows you to determine how the browse results are sorted.<br /><br />  You can sort by ID, Priority, Summary, Open Date, Close Date, Submitter, or Assignee.  You can also have the results sorted in Ascending or Descending order.'));
		case 'new_artifact_type_id':
			return( _('The Data Type option determines the type of tracker item this is.  Since the tracker rolls into one the bug, patch, support, etc... managers you need to be able to determine which one of these an item should belong.<br /><br />This has the added benefit of enabling an admin to turn a support request into a bug.'));
		case 'priority':
			return( _('The priority option allows a user to define a tracker item priority (ranging from 1-Lowest to 5-Highest).<br /><br />This is especially helpful for bugs and support requests where a user might find a critical problem with a project.'));
		case 'resolution':
			return( _('Resolution'));
		case 'summary':
			return( _('The summary text-box represents a short tracker item summary. Useful when browsing through several tracker items.'));
		case 'canned_response':
			return( _('The canned response drop-down represents a list of project admin-defined canned responses to common support or bug submission.<br /><br /> If you are a project admin you can click the \'(admin)\' link to define your own canned responses'));
		case 'comment':
			return( _('Anyone can add here comments to give additional information, answers and solutions. Please, be as precise as possible to avoid misunderstanding. If relevant, screenshots or documents can be added as attached files.'));
		case 'description':
			return( htmlentities(_('Enter the complete description.').'<br/><br/>'.
			_("<div align=\"left\"><b>Editing tips:</b><br/><strong>http,https or ftp</strong>: Hyperlinks.<br/><strong>[#NNN]</strong>: Tracker id NNN.<br/><strong>[TNNN]</strong>: Task id NNN.<br/><strong>[wiki:&lt;pagename&gt;]</strong>: Wiki page.<br/><strong>[forum:&lt;msg_id&gt;]</strong>: Forum post.</div>"),
				ENT_COMPAT, 'UTF-8'));
		case 'attach_file':
			return( _('When you wish to attach a file to a tracker item you must check this checkbox before submitting changes.'));
		case 'monitor':
			return( htmlentities(_('You can monitor or un-monitor this item by clicking the "Monitor" button. <br /><br /><strong>Note!</strong> this will send you additional email. If you add comments to this item, or submitted, or are assigned this item, you will also get emails for those reasons as well!'),
				ENT_COMPAT, 'UTF-8'));
		default:
			return('');
	}
}

function html_use_tooltips() {
	use_javascript('/scripts/jquery/jquery-1.4.2.min.js');
	use_javascript('/scripts/jquery-tipsy/src/javascripts/jquery.tipsy.js');
	use_javascript('/js/tooltips.js');
	use_stylesheet('/scripts/jquery-tipsy/src/stylesheets/tipsy.css');
}

function html_use_storage() {
	use_javascript('/scripts/jquery/jquery-1.4.2.min.js');
	use_javascript('/scripts/jquery-storage/jquery.Storage.js');
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
 * @param		bool	Whether or not to show the 'Any row'
 * @param		string	What to call the 'Any row' defaults to any
 * @param		array	Array of all allowed values from the full list.
 */
function html_build_select_box_from_arrays ($vals,$texts,$select_name,$checked_val='xzxz',$show_100=true,$text_100='none',$show_any=false,$text_any='any', $allowed=false) {
	if ($text_100=='none'){
		$text_100=_('None');
	}
	$return = '';

	$rows=count($vals);
	if (count($texts) != $rows) {
		$return .= 'ERROR - uneven row counts';
	}

	$title = html_get_tooltip_description($select_name);
	$id = '';
	if ($title) {
		$id = ' id="tracker-'.$select_name.'"';
		if (preg_match('/\[\]/', $id)) {
			$id = '';
		}
	}

	$title = html_get_tooltip_description($select_name);
	$return .= '
		<select'.$id.' name="'.$select_name.'" title="'.$title.'">';

	//we don't always want the default Any row shown
	if ($show_any) {
		$return .= '
		<option value=""'.(($checked_val=='') ? ' selected="selected"' : '').'>'. $text_any .'</option>';
	}
	//we don't always want the default 100 row shown
	if ($show_100) {
		$return .= '
		<option value="100"'.(($checked_val==100) ? ' selected="selected"' : '').'>'. $text_100 .'</option>';
	}

	$checked_found=false;

	for ($i=0; $i<$rows; $i++) {
		//  uggh - sorry - don't show the 100 row
		//  if it was shown above, otherwise do show it
		if (($vals[$i] != '100') || ($vals[$i] == '100' && !$show_100)) {
			$return .= '
				<option value="'.$vals[$i].'"';
			if ((string)$vals[$i] == (string)$checked_val) {
				$checked_found=true;
				$return .= ' selected="selected"';
			}
			if (is_array($allowed) && !in_array($vals[$i], $allowed)) {
				$return .= ' disabled="disabled" class="option_disabled"';
			}
			$return .= '>'./*htmlspecialchars(*/$texts[$i]/*)*/.'</option>';
		}
	}
	//
	//	If the passed in "checked value" was never "SELECTED"
	//	we want to preserve that value UNLESS that value was 'xzxz', the default value
	//
	if (!$checked_found && $checked_val != 'xzxz' && $checked_val && $checked_val != 100) {
		$return .= '
		<option value="'.$checked_val.'" selected="selected">'._('No Change').'</option>';
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
function html_build_select_box ($result, $name, $checked_val="xzxz",$show_100=true,$text_100='none',$show_any=false,$text_any='Select One') {
	if ($text_100=='none'){
		$text_100=_('None');
	}
	return html_build_select_box_from_arrays (util_result_column_to_array($result,0),util_result_column_to_array($result,1),$name,$checked_val,$show_100,$text_100, $show_any, $text_any);
}

/**
 * html_build_select_box_sorted() - Takes a result set, with the first column being the "id" or value and
 * the second column being the text you want displayed.
 *
 * @param		int		The result set
 * @param		string	Text to be displayed
 * @param		string	The item that should be checked
 * @param		bool	Whether or not to show the '100 row'
 * @param		string	What to call the '100 row'.  Defaults to none.
 */
function html_build_select_box_sorted ($result, $name, $checked_val="xzxz",$show_100=true,$text_100='none') {
	if ($text_100=='none'){
		$text_100=_('None');
	}
	$vals = util_result_column_to_array($result, 0);
	$texts = util_result_column_to_array($result, 1);
	array_multisort($texts, SORT_ASC, SORT_STRING,
	                $vals);
	return html_build_select_box_from_arrays ($vals, $texts, $name, $checked_val, $show_100, $text_100);
}

/**
 * html_build_multiple_select_box() - Takes a result set, with the first column being the "id" or value
 * and the second column being the text you want displayed.
 *
 * @param		int	The result set
 * @param		string	Text to be displayed
 * @param		string	The item that should be checked
 * @param		int		The size of this box
 * @param		bool	Whether or not to show the '100 row'
 */
function html_build_multiple_select_box ($result,$name,$checked_array,$size='8',$show_100=true) {
	$checked_count=count($checked_array);
	$return = '
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
		$return .= '>'._('None').'</option>';
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
			$return .= '>'. substr(db_result($result,$i,1),0,35). '</option>';
		}
	}
	$return .= '
		</select>';
	return $return;
}

/**
 * html_build_multiple_select_box_from_arrays() - Takes two arrays and builds a multi-select box
 *
 * @param		array	id of the field
 * @param		array	Text to be displayed
 * @param		string	id of the items selected
 * @param		string	The item that should be checked
 * @param		int		The size of this box
 * @param		bool	Whether or not to show the '100 row'
 */
function html_build_multiple_select_box_from_arrays($ids,$texts,$name,$checked_array,$size='8',$show_100=true,$text_100='none') {
	$checked_count=count($checked_array);
	$return ='
		<select name="'.$name.'" multiple="multiple" size="'.$size.'">';
	if ($show_100) {
		if ($text_100=='none') {
			$text_100=_('None');
		}
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
		$return .= '>'.$text_100.'</option>';
	}

	$rows=count($ids);
	for ($i=0; $i<$rows; $i++) {
		if (( $ids[$i] != '100') || ($ids[$i] == '100' && !$show_100)) {
			$return .='
				<option value="'.$ids[$i].'"';
			/*
				Determine if it's checked
			*/
			$val=$ids[$i];
			for ($j=0; $j<$checked_count; $j++) {
				if ($val == $checked_array[$j]) {
					$return .= ' selected="selected"';
				}
			}
			$return .= '>'.$texts[$i].' </option>';
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
 * build_priority_select_box() - Wrapper for html_build_priority_select_box()
 *
 * @see html_build_priority_select_box()
 */
function build_priority_select_box ($name='priority', $checked_val='3', $nochange=false) {
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
function html_build_priority_select_box ($name='priority', $checked_val='3', $nochange=false) {
?>
	<select id="tracker-<?php echo $name ?>" name="<?php echo $name; ?>" title="<?php echo html_get_tooltip_description($name) ?>">
<?php if($nochange) { ?>
	<option value="100"<?php if ($nochange) {echo " selected=\"selected\"";} ?>><?php echo _('No Change') ?></option>
<?php }  ?>
	<option value="1"<?php if ($checked_val=="1") {echo " selected=\"selected\"";} ?>>1 - <?php echo _('Lowest') ?></option>
	<option value="2"<?php if ($checked_val=="2") {echo " selected=\"selected\"";} ?>>2</option>
	<option value="3"<?php if ($checked_val=="3") {echo " selected=\"selected\"";} ?>>3</option>
	<option value="4"<?php if ($checked_val=="4") {echo " selected=\"selected\"";} ?>>4</option>
	<option value="5"<?php if ($checked_val=="5") {echo " selected=\"selected\"";} ?>>5 - <?php echo _('Highest') ?></option>
	</select>
<?php

}

/**
 * html_buildcheckboxarray() - Build an HTML checkbox array.
 *
 * @param	array	Options array
 * @param	name	Checkbox name
 * @param	array	Array of boxes to be pre-checked
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
 * site_header() - everything required to handle security and
 * add navigation for user pages like /my/ and /account/
 *
 * @param	array	Must contain $user_id
 */
function site_header($params) {
	GLOBAL $HTML;
	/*
		Check to see if active user
		Check to see if logged in
	*/
	echo $HTML->header($params);
}

/**
 * site_footer() - Show the HTML site footer.
 *
 * @param	array	Footer params array
 */
function site_footer($params) {
	global $HTML;
	$HTML->footer($params);
}

/**
 * site_project_header() - everything required to handle
 * security and state checks for a project web page
 *
 * @param	params	array() must contain $toptab and $group
 */
function site_project_header($params) {
	global $HTML;

	/*
		Check to see if active
		Check to see if project rather than foundry
		Check to see if private (if private check if user_ismember)
	*/

	$group_id=$params['group'];

	//get the project object
	$project = group_get_object($group_id);

	if (!$project || !is_object($project)) {
		exit_no_group();
	} else if ($project->isError()) {
		if ($project->isPermissionDeniedError()) {
			if (!session_get_user()) {
 			$next = '/account/login.php?error_msg='.urlencode($project->getErrorMessage());
 			if (getStringFromServer('REQUEST_METHOD') != 'POST') {
				$next .= '&return_to='.urlencode(getStringFromServer('REQUEST_URI'));
 			}
			session_redirect($next);
		}
			else
				exit_error(sprintf(_('Project access problem: %s'),$project->getErrorMessage()),'home');
		}
		exit_error(sprintf(_('Project Problem: %s'),$project->getErrorMessage()),'home');
	}

	// Check permissions in case of restricted access
	session_require_perm ('project_read', $group_id);

	//for dead projects must be member of admin project
	if (!$project->isActive()) {
		session_require_global_perm ('forge_admin');
	}

	if (isset($params['title'])){
		$h1=$params['title'];
		$params['title']=$project->getPublicName().': '.$params['title'];
	} else {
		$h1=$project->getPublicName();
		$params['title']=$project->getPublicName();
	}
	if (!isset($params['h1'])){
		$params['h1'] = $h1;
	}

	site_header($params);
}

/**
 * site_project_footer() - currently a simple shim
 * that should be on every project page,  rather than
 * a direct call to site_footer() or theme_footer()
 *
 * @param	params	array() empty
 */
function site_project_footer($params) {
	site_footer($params);
}

/**
 * site_user_header() - everything required to handle security and
 * add navigation for user pages like /my/ and /account/
 *
 * @param	params	array() must contain $user_id
 */
function site_user_header($params) {
	GLOBAL $HTML;

	/*
		Check to see if active user
		Check to see if logged in
	*/
	site_header($params);
	echo ($HTML->beginSubMenu());
	$arr_t = array();
	$all_l = array();
	$arr_attr = array();

	$user = session_get_user();
	$use_tooltips = $user->usesTooltips();

	$arr_t[] = _('My Personal Page');
	$arr_l[] = '/my/';
	if ($use_tooltips) {
		$arr_attr[] = array('title' => _('View your personal page, a selection of widgets to follow the informations from projects.'));
	} else {
		$arr_attr[] = array();
	}

	$arr_t[] = _('Trackers dashboard');
	$arr_l[] = '/my/dashboard.php';
	if ($use_tooltips) {
		$arr_attr[] = array('title' => _('View your tasks and artifacts.'));
	} else {
		$arr_attr[] = array();
	}

	if (forge_get_config('use_diary')) {
		$arr_t[] = _('Diary &amp; Notes');
		$arr_l[] = '/my/diary.php';
		if ($use_tooltips) {
			$arr_attr[] = array('title' => _('Manage your diary. Add, modify or delete your notes.'));
		} else {
			$arr_attr[] = array();
		}
	}

	$arr_t[] = _('Account Maintenance');
	$arr_l[] = '/account/';
	if ($use_tooltips) {
		$arr_attr[] = array('title' => _('Manage your account. Change your password, select your preferences.'));
	} else {
		$arr_attr[] = array();
	}

	if (!forge_get_config('project_registration_restricted')
			|| forge_check_global_perm('approve_projects', '')) {
		$arr_t[] = _('Register Project');
		$arr_l[] = '/register/';
		if ($use_tooltips) {
			$arr_attr[] = array('title' => _('Register a new project in forge, following the workflow.'));
		} else {
			$arr_attr[] = array();
		}
	}

	echo ($HTML->printSubMenu($arr_t, $arr_l, $arr_attr));
	plugin_hook("usermenu", false);
	echo ($HTML->endSubMenu());
}

/**
 * site_user_footer() - currently a simple shim that should be on every user page,
 * rather than a direct call to site_footer() or theme_footer()
 *
 * @param	params	array() empty
 */
function site_user_footer($params) {
	site_footer($params);
}

/**
 * html_clean_hash_string() - Remove noise characters from hex hash string
 *
 * Thruout SourceForge, URLs with hexadecimal hash string parameters
 * are being sent via email to request confirmation of user actions.
 * It was found that some mail clients distort this hash, so we take
 * special steps to encode it in the way which help to preserve its
 * recognition. This routine
 *
 * @param	hashstr	required hash parameter as received from browser
 * @return	pure hex string
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

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>