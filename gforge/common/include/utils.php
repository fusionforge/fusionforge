<?php
/**
 *
 * utils.php - Misc utils common to all aspects of the site
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 *
 */


/**
 * util_check_fileupload() - determines if a filename is appropriate for upload
 *
 * @param	   string  The name of the file being uploaded
 */
function util_check_fileupload($filename) {

	/* Empty file is a valid file.
	This is because this function should be called
	unconditionally at the top of submit action processing
	and many forms have optional file upload. */
	if ($filename == 'none' || $filename == '') {
		return true;
	}

	/* This should be enough... */
	if (!is_uploaded_file($filename)) {
		return false;
	}
	/* ... but we'd rather be paranoic */
	if (strstr($filename, '..')) {
		return false;
	}
	if (!is_file($filename)) {
		return false;
	}
	if (!file_exists($filename)) {
		return false;
	}
	if (dirname($filename) != '/tmp') {
		return false;
	}
	return true;
}

/**
 * util_send_message() - Send email
 * This function should be used in place of the PHP mail() function
 *
 * @param		string	The email recipients address
 * @param		string	The email subject
 * @param		string	The body of the email message
 * @param		string	The optional email sender address.  Defaults to 'noreply@'
 * @param		string	The addresses to blind-carbon-copy this message
 *
 */
function util_send_message($to,$subject,$body,$from='',$BCC='') {
	if (!$to) {
		$to='noreply@'.$GLOBALS['sys_default_domain'];
	}
	if (!$from) {
		$from='noreply@'.$GLOBALS['sys_default_domain'];
	}
	if (!$to) {
		$to='noreply@'.$GLOBALS['sys_default_domain'];
	}
	$body = "To: $to".
		"\nFrom: $from".
		"\nBCC: $BCC".
		"\nSubject: $subject".
		"\n\n$body";

	exec ("/bin/echo \"". util_prep_string_for_sendmail($body) .
		  "\" | /usr/sbin/sendmail -f'$from' -t -i >& /dev/null &");
}

function util_send_jabber($to,$subject,$body) {
	if (!$GLOBALS['sys_use_jabber']) {
		return;
	}
	$JABBER = new Jabber();
	if (!$JABBER->Connect()) {
		echo '<br />Unable to connect';
		return false;
	}
	//$JABBER->SendAuth();
	//$JABBER->AccountRegistration();
	if (!$JABBER->SendAuth()) {
		echo '<br />Auth Failure';
		$JABBER->Disconnect();
		return false;
		//or die("Couldn't authenticate!");
	}
	$JABBER->SendPresence(NULL, NULL, "online");

	$body=htmlspecialchars($body);
	$to_arr=explode(',',$to);
	for ($i=0; $i<count($to_arr); $i++) {
		if ($to_arr[$i]) {
			//echo '<br />Sending Jabbers To: '.$to_arr[$i];
			if (!$JABBER->SendMessage($to_arr[$i], "normal", NULL, array("body" => $body,"subject"=>$subject))) {
				echo '<br />Error Sending to '.$to_arr[$i];
			}
		}
	}

	$JABBER->CruiseControl(2);
	$JABBER->Disconnect();
}

/**
 * util_prep_string_for_sendmail() - Prepares a string to be sent by email
 *
 * @param		string	The text to be prepared
 * @returns The prepared text
 *
 */
function util_prep_string_for_sendmail($body) {
	//$body=str_replace("\\","\\\\",$body);
	$body=str_replace("`","\\`",$body);
	$body=str_replace("\"","\\\"",$body);
	$body=str_replace("\$","\\\$",$body);
	return $body;
}

/**
 *	util_handle_message() - a convenience wrapper which sends messages
 *	to either a jabber account or email account or both, depending on 
 *	user preferences
 *
 *	@param	array	array of user_id's from the user table
 *	@param	string	subject of the message
 *	@param	string	the message body
 *	@param	string	a comma-separated list of email address
 *	@param	string	a comma-separated list of jabber address
 */
function util_handle_message($id_arr,$subject,$body,$extra_emails='',$extra_jabbers='') {
	$address=array();

	if (count($id_arr) < 1) {
		
	} else {
		$res=db_query("SELECT jabber_address,email,jabber_only
			FROM users WHERE user_id IN (". implode($id_arr,',') .")");
		$rows=db_numrows($res);

		for ($i=0; $i<$rows; $i++) {
			//
			//  Build arrays of the jabber address
			//
			if (db_result($res,$i,'jabber_address')) {
				$address['jabber_address'][]=db_result($res,$i,'jabber_address');
				if (db_result($res,$i,'jabber_only') != 1) {
					$address['email'][]=db_result($res,$i,'email');
				}
			} else {
				$address['email'][]=db_result($res,$i,'email');
			}
		}
		if (count($address['email']) > 0) {
			$extra_email1=implode($address['email'],',').',';
		}
		if (count($address['jabber_address']) > 0) {
			$extra_jabber1=implode($address['jabber_address'],',').',';
		}
	}
	if ($extra_email1 || $extra_emails) {
		util_send_message('',$subject,$body,'',$extra_email1.$extra_emails);
	}
	if ($extra_jabber1 || $extra_jabbers) {
		util_send_jabber($extra_jabber1.$extra_jabbers,$subject,$body);
	}
}

/**
 * util_unconvert_htmlspecialchars() - Unconverts a string converted with htmlspecialchars()
 * This function requires PHP 4.0.3 or greater
 *
 * @param		string	The string to unconvert
 * @returns The unconverted string
 *
 */
function util_unconvert_htmlspecialchars($string) {
	if (strlen($string) < 1) {
		return '';
	} else {
		//$trans = get_html_translation_table(HTMLENTITIES, ENT_QUOTES); 
		$trans = get_html_translation_table(HTML_ENTITIES); 
		$trans = array_flip ($trans);
		$str = strtr ($string, $trans);
		return $str;
	}
}

/**
 * util_result_columns_to_assoc() - Takes a result set and turns the column pair into an associative array
 *
 * @param		string	The result set ID
 * @param		int		The column key
 * @param		int		The optional column value
 * @returns An associative array
 *
 */
function util_result_columns_to_assoc($result, $col_key=0, $col_val=1) {
	$rows=db_numrows($result);

	if ($rows > 0) {
		$arr=array();
		for ($i=0; $i<$rows; $i++) {
			$arr[db_result($result,$i,$col_key)]=db_result($result,$i,$col_val);
		}
	} else {
		$arr=array();
	}
	return $arr;
}

/**
 * util_result_column_to_array() - Takes a result set and turns the optional column into an array
 *
 * @param		int		The result set ID
 * @param		int		The column
 * @resturns An array
 *
 */
function &util_result_column_to_array($result, $col=0) {
	/*
		Takes a result set and turns the optional column into
		an array
	*/
	$rows=db_numrows($result);

	if ($rows > 0) {
		$arr=array();
		for ($i=0; $i<$rows; $i++) {
			$arr[$i]=db_result($result,$i,$col);
		}
	} else {
		$arr=array();
	}
	return $arr;
}

/**
 * result_column_to_array() - DEPRECATED; DO NOT USE!
 *
 * @param		int		The result set ID
 * @param		int		The column
 * @see util_result_column_to_array()
 * @deprecated
 *
 */
function &result_column_to_array($result, $col=0) {
	/*
		backwards compatibility
	*/
	return util_result_column_to_array($result, $col);
}

/**
 * util_wrap_find_space() - Find the first space in a string
 *
 * @param		string	The string in which to find the space
 * @param		int		The number of characters to wrap - Default is 80
 * @returns The position of the first space
 *
 */
function util_wrap_find_space($string,$wrap) {
	//echo"\n";
	$start=$wrap-5;
	$try=1;
	$found=false;
	
	while (!$found) {
		//find the first space starting at $start
		$pos=@strpos($string,' ',$start);
		
		//if that space is too far over, go back and start more to the left
		if (($pos > ($wrap+5)) || !$pos) {
			$try++;
			$start=($wrap-($try*5));
			//if we've gotten so far left , just truncate the line
			if ($start<=10) {
				return $wrap;
			}	   
			$found=false;
		} else {
			$found=true;
		}	   
	}	   
	
	return $pos;
}

/**
 * util_line_wrap() - Automatically linewrap text
 *
 * @param		string	The text to wrap
 * @param		int		The number of characters to wrap - Default is 80
 * @param		string	The line break to use - Default is '\n'
 * @returns The wrapped text
 *
 */
function util_line_wrap ($text, $wrap = 80, $break = "\n") {
	$paras = explode("\n", $text);
			
	$result = array();
	$i = 0;
	while ($i < count($paras)) {
		if (strlen($paras[$i]) <= $wrap) {
			$result[] = $paras[$i];
			$i++;
		} else {
			$pos=util_wrap_find_space($paras[$i],$wrap);
			
			$result[] = substr($paras[$i], 0, $pos);
			
			$new = trim(substr($paras[$i], $pos, strlen($paras[$i]) - $pos));
			if ($new != '') {
				$paras[$i] = $new;
				$pos=util_wrap_find_space($paras[$i],$wrap);
			} else {
				$i++;
			}	   
		}	   
	}			   
	return implode($break, $result);
}

/**
 * util_make_links() - Turn URL's into HREF's.
 *
 * @param		string	The URL
 * @returns The HREF'ed URL
 *
 */
function util_make_links ($data='') {
	if(empty($data)) { return $data; }

	$lines = split("\n",$data);
	while ( list ($key,$line) = each ($lines)) {
		// When we come here, we usually have form input
		// encoded in entities. Our aim is to NOT include
		// angle brackets in the URL
		// (RFC2396; http://www.w3.org/Addressing/URL/5.1_Wrappers.html)
		$line = str_replace('&gt;', "\1", $line);
		$line = eregi_replace("([ \t]|^)www\."," http://www.",$line);
		$text = eregi_replace("([[:alnum:]]+)://([^[:space:]]*)([[:alnum:]#?/&=])", "<a href=\"\\1://\\2\\3\" target=\"_blank\" target=\"_new\">\\1://\\2\\3</a>", $line);
		$text = eregi_replace("([[:space:]]|^)(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))", "\\1<a href=\"mailto:\\2\" target=\"_new\">\\2</a>", $text);
		$text = str_replace("\1", '&gt;', $text);
		$newText .= $text;
	}
	return $newText;
}

/**
 * show_priority_colors_key() - Show the priority colors legend
 *
 */
function show_priority_colors_key() {

	echo '<p><strong>Priority Colors:</strong><br />

		<table border=0><tr>';

	for ($i=1; $i<10; $i++) {
		echo '
			<td bgcolor="'.get_priority_color($i).'">'.$i.'</td>';
	}
	echo '</tr></table>';
}

/**
 * utils_buildcheckboxarray() - Build a checkbox array
 *
 * @param		int		Number of options to be in the array
 * @param		string	The name of the checkboxes
 * @param		array	An array of boxes to be pre-checked
 * 
 */
function utils_buildcheckboxarray($options,$name,$checked_array) {
	$option_count=count($options);
	$checked_count=count($checked_array);

	for ($i=1; $i<=$option_count; $i++) {
		echo '
			<br /><input type="checkbox" name="'.$name.'" value="'.$i.'"';
		for ($j=0; $j<$checked_count; $j++) {
			if ($i == $checked_array[$j]) {
				echo ' CHECKED';
			}
		}
		echo '> '.$options[$i];
	}
}

/**
 * GraphResult() - Takes a database result set and builds a graph.
 * The first column should be the name, and the second column should be the values
 * Be sure to include HTL_Graphs.php before using this function
 * 
 * @author Tim Perdue tperdue@valinux.com
 * @param		int		The databse result set ID
 * @param		string	The title of the graph
 * 
 */
Function GraphResult($result,$title) {
	$rows=db_numrows($result);

	if ((!$result) || ($rows < 1)) {
		echo 'None Found.';
	} else {
		$names=array();
		$values=array();

		for ($j=0; $j<db_numrows($result); $j++) {
			if (db_result($result, $j, 0) != '' && db_result($result, $j, 1) != '' ) {
				$names[$j]= db_result($result, $j, 0);
				$values[$j]= db_result($result, $j, 1);
			}
		}

	/*
		This is another function detailed below
	*/
		GraphIt($names,$values,$title);
	}
}

/**
 * GraphIt() - Build a graph
 * 
 * @author Tim Perdue tperdue@valinux.com
 * @param		array	An array of names
 * @param		array	An array of values
 * @param		string	The title of the graph
 *
 */
Function GraphIt($name_string,$value_string,$title) {
	GLOBAL $HTML;

	$counter=count($name_string);

	/*
		Can choose any color you wish
	*/
	$bars=array();

	for ($i = 0; $i < $counter; $i++) {
		$bars[$i]=$HTML->COLOR_LTBACK1;
	}

	$counter=count($value_string);

	/*
		Figure the max_value passed in, so scale can be determined
	*/

	$max_value=0;

	for ($i = 0; $i < $counter; $i++) {
		if ($value_string[$i] > $max_value) {
			$max_value=$value_string[$i];
		}
	}

	if ($max_value < 1) {
		$max_value=1;
	}

	/*
		I want my graphs all to be 800 pixels wide, so that is my divisor
	*/

	$scale=(400/$max_value);

	/*
		I create a wrapper table around the graph that holds the title
	*/

	$title_arr=array();
	$title_arr[]=$title;

	echo $GLOBALS['HTML']->listTableTop ($title_arr);
	echo '<tr><td>';
	/*
		Create an associate array to pass in. I leave most of it blank
	*/

	$vals =  array(
	'vlabel'=>'',
	'hlabel'=>'',
	'type'=>'',
	'cellpadding'=>'',
	'cellspacing'=>'0',
	'border'=>'',
	'width'=>'',
	'background'=>'',
	'vfcolor'=>'',
	'hfcolor'=>'',
	'vbgcolor'=>'',
	'hbgcolor'=>'',
	'vfstyle'=>'',
	'hfstyle'=>'',
	'noshowvals'=>'',
	'scale'=>$scale,
	'namebgcolor'=>'',
	'valuebgcolor'=>'',
	'namefcolor'=>'',
	'valuefcolor'=>'',
	'namefstyle'=>'',
	'valuefstyle'=>'',
	'doublefcolor'=>'');

	/*
		This is the actual call to the HTML_Graphs class
	*/

	html_graph($name_string,$value_string,$bars,$vals);

	echo '
		</td></tr>
		<!-- end outer graph table -->';
	echo $GLOBALS['HTML']->listTableBottom();
}

/**
 * ShowResultSet() - Show a generic result set
 * Very simple, plain way to show a generic result set
 *
 * @param		int		The result set ID
 * @param		string	The title of the result set
 * @param		bool	The option to turn URL's into links
 * 
 */
Function  ShowResultSet($result,$title="Untitled",$linkify=false)  {
	global $group_id,$HTML;

	if  ($result)  {
		$rows  =  db_numrows($result);
		$cols  =  db_numfields($result);

		echo '
			<table border="0" width="100%">';

		/*  Create the title  */

		$cell_data=array();
		$cell_data[] = array($title, 'colspan='.$cols);
		echo $HTML->multiTableRow('',$cell_data, TRUE);

		/*  Create  the  headers  */
		$cell_data=array();
		for ($i=0; $i < $cols; $i++) {
			$cell_data[] = array(db_fieldname($result,$i));
		}
		echo $HTML->multiTableRow('',$cell_data, TRUE);
		
		/*  Create the rows  */
		for ($j = 0; $j < $rows; $j++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '>';
			for ($i = 0; $i < $cols; $i++) {
				if ($linkify && $i == 0) {
					$link = '<a href="'.$PHP_SELF.'?';
					$linkend = '</a>';
					if ($linkify == "bug_cat") {
						$link .= 'group_id='.$group_id.'&bug_cat_mod=y&bug_cat_id='.db_result($result, $j, 'bug_category_id').'">';
					} else if($linkify == "bug_group") {
						$link .= 'group_id='.$group_id.'&bug_group_mod=y&bug_group_id='.db_result($result, $j, 'bug_group_id').'">';
					} else if($linkify == "patch_cat") {
						$link .= 'group_id='.$group_id.'&patch_cat_mod=y&patch_cat_id='.db_result($result, $j, 'patch_category_id').'">';
					} else if($linkify == "support_cat") {
						$link .= 'group_id='.$group_id.'&support_cat_mod=y&support_cat_id='.db_result($result, $j, 'support_category_id').'">';
					} else if($linkify == "pm_project") {
						$link .= 'group_id='.$group_id.'&project_cat_mod=y&project_cat_id='.db_result($result, $j, 'group_project_id').'">';
					} else {
						$link = $linkend = '';
					}
				} else {
					$link = $linkend = '';
				}
				echo '<td>'.$link . db_result($result,  $j,  $i) . $linkend.'</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo db_error();
	}
}

/**
 * validate_email() - Validate an email address
 *
 * @param		string	The address string to validate
 * @returns true on success/false on error
 *
 */
function validate_email ($address) {
	return (ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'. '@'. '[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' . '[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $address));
}

/**
 * util_is_valid_filename() - Verifies whether a file has a valid filename
 *
 * @param		string	The file to verify
 * @returns true on success/false on error
 *
 */
function util_is_valid_filename ($file) {
	//bad char test
	$invalidchars = eregi_replace("[-A-Z0-9_\.]","",$file);

	if (!empty($invalidchars)) {
		return false;
	} else {
		if (strstr($file,'..')) {
			return false;
		} else {
			return true;
		}
	}
}

/**
 * valid_hostname() - Validates a hostname string to make sure it doesn't contain invalid characters
 * 
 * @param		string	The optional hostname string
 * @returns true on success/false on failur
 *
 */
function valid_hostname ($hostname = "xyz") {

	//bad char test
	$invalidchars = eregi_replace("[-A-Z0-9\.]","",$hostname);

	if (!empty($invalidchars)) {
		return false;
	}

	//double dot, starts with a . or -
	if (ereg("\.\.",$hostname) || ereg("^\.",$hostname) || ereg("^\-",$hostname)) {
		return false;
	}

	$multipoint = explode(".",$hostname);

	if (!(is_array($multipoint)) || ((count($multipoint) - 1) < 1)) {
		return false;
	}

	return true;

}

?>
