<?php
/**
 * FusionForge miscellaneous utils
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2009-2010, Roland Mas
 * Copyright 2009-2010, Franck Villaume - Capgemini
 * Copyright (c) 2010, 2011
 *	Thorsten Glaser <t.glaser@tarent.de>
 * Copyright (C) 2010-2011 Alain Peyrat - Alcatel-Lucent
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
 * htpasswd_apr1_md5($plainpasswd) - generate htpasswd md5 format password
 *
 * From http://www.php.net/manual/en/function.crypt.php#73619
 */
function htpasswd_apr1_md5($plainpasswd) {
    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
    $len = strlen($plainpasswd);
    $text = $plainpasswd.'$apr1$'.$salt;
    $bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
    $tmp = '';
    for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
    for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
    $bin = pack("H32", md5($text));
    for($i = 0; $i < 1000; $i++) {
        $new = ($i & 1) ? $plainpasswd : $bin;
        if ($i % 3) $new .= $salt;
        if ($i % 7) $new .= $plainpasswd;
        $new .= ($i & 1) ? $bin : $plainpasswd;
        $bin = pack("H32", md5($new));
    }
    for ($i = 0; $i < 5; $i++) {
        $k = $i + 6;
        $j = $i + 12;
        if ($j == 16) $j = 5;
        $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
    }
    $tmp = chr(0).chr(0).$bin[11].$tmp;
    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
    return "$"."apr1"."$".$salt."$".$tmp;
}
 
/**
 * is_utf8($string) - utf-8 detection
 *
 * From http://www.php.net/manual/en/function.mb-detect-encoding.php#85294
 */
function is_utf8($str) {
    $c=0; $b=0;
    $bits=0;
    $len=strlen($str);
    for($i=0; $i<$len; $i++){
        $c=ord($str[$i]);
        if($c > 128){
            if(($c >= 254)) return false;
            elseif($c >= 252) $bits=6;
            elseif($c >= 248) $bits=5;
            elseif($c >= 240) $bits=4;
            elseif($c >= 224) $bits=3;
            elseif($c >= 192) $bits=2;
            else return false;
            if(($i+$bits) > $len) return false;
            while($bits > 1){
                $i++;
                $b=ord($str[$i]);
                if($b < 128 || $b > 191) return false;
                $bits--;
            }
        }
    }
    return true;
}

/**
 * removeCRLF() - remove any Carriage Return-Line Feed from a string. 
 * That function is useful to remove the possibility of a CRLF Injection when sending mail
 * All the data that we will send should be passed through that function
 *
 * @param	   string  The string that we want to empty from any CRLF 
 */
function util_remove_CRLF($str) {
	return strtr($str, "\015\012", '  ');
}


/**
 * util_check_fileupload() - determines if a filename is appropriate for upload
 *
 * @param	   array  The uploaded file as returned by getUploadedFile()
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
	if ((dirname($filename) != '/tmp') &&
            (dirname($filename) != "/var/tmp")) {
		return false;
	}
	return true;
}

/**
 * util_check_url() - determines if given URL is valid.
 *
 * Currently, test is very basic, only the protocol is
 * checked, allowed values are: http, https, ftp.
 *
 * @param		string  The URL
 * @return		boolean	true if valid, false if not valid.
 */
function util_check_url($url) {
	return (preg_match('/^(http|https|ftp):\/\//', $url) > 0);
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
 * @param		string	The optional email sender name. Defaults to ''
 * @param 		boolean	Whether to send plain text or html email
 *
 */
function util_send_message($to,$subject,$body,$from='',$BCC='',$sendername='',$extra_headers='',$send_html_email=false) {


	if (!$to) {
		$to='noreply@'.forge_get_config('web_host');
	}
	if (!$from) {
		$from='noreply@'.forge_get_config('web_host');
	}
	

	$charset = _('UTF-8');
	if (!$charset) {
		$charset = 'UTF-8';
	}

	$body2 = '';
	if ($extra_headers) {
		$body2 .= $extra_headers."\n";
	}
	$body2 .= "To: $to".
		"\nFrom: ".util_encode_mailaddr($from,$sendername,$charset);
	if (forge_get_config('bcc_all_emails') != '') {
		$BCC.=",".forge_get_config('bcc_all_emails');
	}
	if(!empty($BCC)) {
		$body2 .= "\nBCC: $BCC";
	}
	$send_html_email?$type="html":$type="plain";
	$body2 .= "\n".util_encode_mimeheader("Subject", $subject, $charset).
		"\nContent-type: text/$type; charset=$charset".
		"\n\n".
		util_convert_body($body, $charset);
	
	if (!forge_get_config('sendmail_path')){
		$sys_sendmail_path="/usr/sbin/sendmail";
	}

 	$handle = popen(forge_get_config('sendmail_path')." -f'$from' -t -i", 'w');
	fwrite ($handle, $body2);
 	pclose($handle);
}

/**
 * util_encode_mailaddr() - Encode email address to MIME format
 *
 * @param		string	The email address
 * @param		string	The email's owner name
 * @param		string	The converting charset
 *
 */
function util_encode_mailaddr($email,$name,$charset) {
	if (function_exists('mb_convert_encoding') && trim($name) != "") {
		$name = "=?".$charset."?B?".
			base64_encode(mb_convert_encoding(
				$name,$charset,"UTF-8")).
			"?=";
	}
	
	return $name." <".$email."> ";
}

/**
 * util_encode_mimeheader() - Encode mimeheader
 *
 * @param		string	The name of the header (e.g. "Subject")
 * @param		string	The email subject
 * @param		string	The converting charset (like ISO-2022-JP)
 * @return		string	The MIME encoded subject
 *
 */
function util_encode_mimeheader($headername,$str,$charset) {
	if (function_exists('mb_internal_encoding') &&
	    function_exists('mb_encode_mimeheader')) {
		$x = mb_internal_encoding();
		mb_internal_encoding("UTF-8");
		$y = mb_encode_mimeheader($headername . ": " . $str,
					  $charset, "Q");
		mb_internal_encoding($x);
		return $y;
	}

	if (!function_exists('mb_convert_encoding')) {
		return $headername . ": " . $str;
	}

	return $headername . ": " . "=?".$charset."?B?".
		base64_encode(mb_convert_encoding(
			$str,$charset,"UTF-8")).
		"?=";
}

/**
 * util_convert_body() - Convert body of the email message
 *
 * @param		string	The body of the email message
 * @param		string	The charset of the email message
 * @return		string	The converted body of the email message
 *
 */
function util_convert_body($str,$charset) {
	if (!function_exists('mb_convert_encoding') || $charset == 'UTF-8') {
		return $str;
	}
	
	return mb_convert_encoding($str,$charset,"UTF-8");
}

function util_send_jabber($to,$subject,$body) {
	if (!forge_get_config('use_jabber')) {
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
 *	util_handle_message() - a convenience wrapper which sends messages
 *	to either a jabber account or email account or both, depending on
 *	user preferences
 *
 *	@param	array	array of user_id's from the user table
 *	@param	string	subject of the message
 *	@param	string	the message body
 *	@param	string	a comma-separated list of email address
 *	@param	string	a comma-separated list of jabber address
 *	@param	string	From header
 */
function util_handle_message($id_arr,$subject,$body,$extra_emails='',$extra_jabbers='',$from='') {
	$address=array();

	if (count($id_arr) < 1) {

	} else {
		$res = db_query_params ('SELECT user_id,jabber_address,email,jabber_only FROM users WHERE user_id = ANY ($1)',
					array (db_int_array_to_any_clause ($id_arr))) ;
		$rows = db_numrows($res) ;

		for ($i=0; $i<$rows; $i++) {
			if (db_result($res, $i, 'user_id') == 100) {
				// Do not send messages to "Nobody"
				continue;
			}
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
		if (isset ($address['email']) && count($address['email']) > 0) {
			$extra_emails=implode($address['email'],',').',' . $extra_emails;
		}
		if (isset ($address['jabber_address']) && count($address['jabber_address']) > 0) {
			$extra_jabbers=implode($address['jabber_address'],',').','.$extra_jabbers;
		}
	}
	if ($extra_emails) {
		util_send_message('',$subject,$body,$from,$extra_emails);
	}
	if ($extra_jabbers) {
		util_send_jabber($extra_jabbers,$subject,$body);
	}
}

/**
 * util_unconvert_htmlspecialchars() - Unconverts a string converted with htmlspecialchars()
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
 * util_wrap_find_space() - Find the first space in a string
 *
 * @param		string	The string in which to find the space (must be UTF8!)
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
			if ($start<=20) {
				while ($wrap >= 1) {
					$code = ord(substr($string,$wrap,1));
					if ($code <= 0x7F ||
					    $code >= 0xC0) {
						//Here is single byte character
						//or head of multi byte character  
						return $wrap;
					}
					//Do not break multi byte character
					$wrap--;
				}
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
	if(empty($data)) { 
		return $data; 
	}
	$lines = explode("\n",$data);
	$newText = "";
	while ( list ($key,$line) = each ($lines)) {
		// When we come here, we usually have form input
		// encoded in entities. Our aim is to NOT include
		// angle brackets in the URL
		// (RFC2396; http://www.w3.org/Addressing/URL/5.1_Wrappers.html)
		$line = str_replace('&gt;', "\1", $line);
		$line = preg_replace( "/([ \t]|^)www\./i", " http://www.", $line);
		$text = preg_replace( "/([[:alnum:]]+):\/\/([^[:space:]<\1]*)([[:alnum:]#?\/&=])/i",
			"<a href=\"\\1://\\2\\3\" target=\"_new\">\\1://\\2\\3</a>", $line);
		$text = preg_replace(
			"/([[:space:]]|^)(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))/i",
			"\\1<a href=\"mailto:\\2\" target=\"_new\">\\2</a>",
			$text
			);
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
	echo '<p /><strong> '._('Priority Colors').':</strong><br />

		<table border="0"><tr>';

	for ($i=1; $i<6; $i++) {
		echo '
			<td class="priority'.$i.'">'.$i.'</td>';
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
				echo ' checked';
			}
		}
		echo '> '.$options[$i];
	}
}

/**
 * utils_requiredField() - Adds the required field marker
 *
 * @return	a string holding the HTML to mark a required field
 */
function utils_requiredField() {
	return '<span class="requiredfield">*</span>';
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
function GraphResult($result, $title) {
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
function GraphIt($name_string, $value_string, $title) {
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
 * @param	int		The result set ID
 * @param	string	The title of the result set
 * @param	bool	The option to turn URL's into links
 * @param	bool	The option to display headers
 * @param	array	The db field name -> label mapping
 * @param	array   Don't display these cols
 *
 */
function ShowResultSet($result,$title='',$linkify=false,$displayHeaders=true,$headerMapping=array(), $excludedCols=array())  {
	global $group_id,$HTML;

	if($result)  {
		$rows  =  db_numrows($result);
		$cols  =  db_numfields($result);

		echo '<table border="0" width="100%">';

		/*  Create  the  headers  */
		$headersCellData = array();
		$colsToKeep = array();
		for ($i=0; $i < $cols; $i++) {
			$fieldName = db_fieldname($result, $i);
			if(in_array($fieldName, $excludedCols)) {
				continue;
			}
			$colsToKeep[] = $i;
			if(isset($headerMapping[$fieldName])) {
				if(is_array($headerMapping[$fieldName])) {
					$headersCellData[] = $headerMapping[$fieldName];
				} else {
					$headersCellData[] = array($headerMapping[$fieldName]);
				}
			}
			else {
				$headersCellData[] = array($fieldName);
			}
		}
		
		/*  Create the title  */
		if(strlen($title) > 0) {
			$titleCellData = array();
			$titleCellData[] = array($title, 'colspan="'.count($headersCellData).'"');
			echo $HTML->multiTableRow('', $titleCellData, TRUE);
		}
		
		/* Display the headers */
		if($displayHeaders) {
			echo $HTML->multiTableRow('', $headersCellData, TRUE);
		}

		/*  Create the rows  */
 		for ($j = 0; $j < $rows; $j++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '>';
			for ($i = 0; $i < $cols; $i++) {
				if(in_array($i, $colsToKeep)) {
					if ($linkify && $i == 0) {
						$link = '<a href="'.getStringFromServer('PHP_SELF').'?';
						$linkend = '</a>';
						if ($linkify == "bug_cat") {
							$link .= 'group_id='.$group_id.'&amp;bug_cat_mod=y&amp;bug_cat_id='.db_result($result, $j, 'bug_category_id').'">';
						} else if($linkify == "bug_group") {
							$link .= 'group_id='.$group_id.'&amp;bug_group_mod=y&amp;bug_group_id='.db_result($result, $j, 'bug_group_id').'">';
						} else if($linkify == "patch_cat") {
							$link .= 'group_id='.$group_id.'&amp;patch_cat_mod=y&amp;patch_cat_id='.db_result($result, $j, 'patch_category_id').'">';
						} else if($linkify == "support_cat") {
							$link .= 'group_id='.$group_id.'&amp;support_cat_mod=y&amp;support_cat_id='.db_result($result, $j, 'support_category_id').'">';
						} else if($linkify == "pm_project") {
							$link .= 'group_id='.$group_id.'&amp;project_cat_mod=y&amp;project_cat_id='.db_result($result, $j, 'group_project_id').'">';
						} else {
							$link = $linkend = '';
						}
					} else {
						$link = $linkend = '';
					}
					echo '<td>'.$link . db_result($result,  $j,  $i) . $linkend.'</td>';
				}
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
	if ( preg_match( "/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+$/", $address) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * validate_emails() - Validate a list of e-mail addresses
 *
 * @param	string	E-mail list
 * @param	char	Separator
 * @returns	array	Array of invalid e-mail addresses (if empty, all addresses are OK)
*/
function validate_emails ($addresses, $separator=',') {
	if (strlen($addresses) == 0) return array();
	
	$emails = explode($separator, $addresses);
	$ret 	= array();
	
	if (is_array($emails)) {
		foreach ($emails as $email) {
			$email = trim($email);		// This is done so we can validate lists like "a@b.com, c@d.com"
			if (!validate_email($email)) $ret[] = $email;
		}
	}
	return $ret;
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
	$invalidchars = preg_replace("/[-A-Z0-9+_\. ~]/i","",$file);

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
	$invalidchars = preg_replace("/[-A-Z0-9\.]/i","",$hostname);

	if (!empty($invalidchars)) {
		return false;
	}

	//double dot, starts with a . or -
	if ( preg_match("/\.\./",$hostname) || preg_match("/^\./",$hostname)  || preg_match("/^\-/",$hostname) ) {
		return false;
	}

	$multipoint = explode(".",$hostname);

	if (!(is_array($multipoint)) || ((count($multipoint) - 1) < 1)) {
		return false;
	}

	return true;

}


/**
 * human_readable_bytes() - Translates an integer representing bytes to a human-readable format.
 *
 * Format file size in a human-readable way
 * such as "xx Megabytes" or "xx Mo"
 *
 * @author           Andrea Paleni <andreaSPAMLESS_AT_SPAMLESScriticalbit.com>
 * @version        1.0
 * @param int       bytes   is the size
 * @param bool     base10  enable base 10 representation, otherwise
 *                 default base 2  is used  
 * @param int       round   number of fractional digits
 * @param array     labels  strings associated to each 2^10 or
 *                  10^3(base10==true) multiple of base units
 */
function human_readable_bytes ($bytes, $base10=false, $round=0, $labels=array(' bytes',  ' KB', ' MB', ' GB')) {
	if ($bytes <= 0 || !is_array($labels) || (count($labels) <= 0)) {
		return null;
	}
	$step = $base10 ? 3 : 10;
	$base = $base10 ? 10 : 2;
	$log = (int)(log10($bytes)/log10($base));
	krsort($labels);
	foreach ($labels as $p=>$lab) {
		$pow = $p * $step;
		if ($log < $pow) {
			continue;
		}
		if ($lab == " MB" or $lab == " GB") {
			$round = 2;
		}
		$text = round($bytes/pow($base,$pow),$round).$lab;
		break;
	}
	return $text;
}

/**
 *	ls - lists a specified directory and returns an array of files
 *	@param	string	the path of the directory to list
 *	@param	boolean	whether to filter out directories and illegal filenames
 *	@return	array	array of file names.
 */
function &ls($dir,$filter=false) {
	$out = array();

	if (is_dir($dir) && ($h = opendir($dir))) {
		while (($f = readdir($h)) !== false) {
			if ($f[0] == '.')
				continue;
			if ($filter) {
				if (!util_is_valid_filename($f) ||
				    !is_file($dir . "/" . $f))
					continue;
			}
			$out[] = $f;
		}
		closedir($h);
	}
	return $out;
}

/**
 * readfile_chunked() - replacement for readfile
 *
 * @param		string	The file path
 * @param		bool    Whether to return bytes served or just a bool
 *
 */
function readfile_chunked($filename, $returnBytes=true) {
    $chunksize = 1*(1024*1024); // 1MB chunks
    $buffer = '';
    $byteCounter = 0;
    
    $handle = fopen($filename, 'rb');
    if ($handle === false) {
        return false;
    }
    
    ob_start () ;
    while (!feof($handle)) {
	    $buffer = fread($handle, $chunksize);
	    echo $buffer;
	    ob_flush() ;
	    flush () ;
	    if ($returnBytes) {
		    $byteCounter += strlen($buffer);
	    }
    }
    ob_end_flush () ;
    $status = fclose($handle);
    if ($returnBytes && $status) {
        return $byteCounter; // return num. bytes delivered like readfile() does.
    }
    return $status;
}

/**
 * util_is_root_dir() - Checks if a directory points to the root dir
 * @param	string	Directory
 * @return bool
 */
function util_is_root_dir($dir) {
	return !preg_match('/[^\\/]/',$dir);
}

/**
 * util_is_dot_or_dotdot() - Checks if a directory points to . or ..
 * @param	string	Directory
 * @return bool
 */
function util_is_dot_or_dotdot($dir) {
	return preg_match('/^\.\.?$/',trim($dir, '/'));
}

/**
 * util_containts_dot_or_dotdot() - Checks if a directory containts . or ..
 * @param	string	Directory
 * @return bool
 */
function util_containts_dot_or_dotdot($dir) {
  foreach (explode('/', $dir) as $sub_dir) {
    if (util_is_dot_or_dotdot($sub_dir))
      return true;
  }
  
  return false;
}

/**
 * util_secure_filename() - Returns a secured file name
 * @param	string	Filename
 * @return	string  Filename
 */
function util_secure_filename($file) {
	$f = preg_replace("/[^-A-Z0-9_\.]/i", '', $file);
	if (util_containts_dot_or_dotdot($f))
		$f = preg_replace("/\./", '_', $f);
	if (! $f)
		$f = md5($file);
	return $f;
}

/**
 * util_strip_accents() - Remove accents from given text.
 * @param	string	Text
 * @return 	string
 */
function util_strip_accents($text) {
	return iconv ('UTF-8', 'US-ASCII//TRANSLIT', $text) ;
}

/**
 * Constructs the forge's URL prefix out of forge_get_config('url_prefix')
 * 
 * @return string
 */
function normalized_urlprefix () {
	$prefix = forge_get_config('url_prefix') ;
	$prefix = preg_replace ("/^\//", "", $prefix) ;
	$prefix = preg_replace ("/\/$/", "", $prefix) ;
	$prefix = "/$prefix/" ;
	if ($prefix == '//') 
		$prefix = '/' ;
	return $prefix ;
}

/**
 * Construct full URL from a relative path
 * 
 * @param string $path
 * @return string URL
 */
function util_make_url ($path) {
        if (forge_get_config('use_ssl')) {
                $url = "https://" ;
                $url .= forge_get_config('web_host') ;
                if (forge_get_config('https_port') != 443) {
                        $url .= ":".forge_get_config('https_port') ;
                }
        } else {
                $url = "http://" ;
                $url .= forge_get_config('web_host') ;
                if (forge_get_config('http_port') != 80) {
                        $url .= ":".forge_get_config('http_port') ;
                }
        }
	$url .= util_make_uri ($path) ;
	return $url ;
}

/**
 * Construct proper (relative) URI (prepending prefix)
 * 
 * @param string $path
 * @return string URI
 */
function util_make_uri ($path) {
	$path = preg_replace ('/^\//', '', $path) ;
	$uri = normalized_urlprefix () ;
	$uri .= $path ;
	return $uri ;
}

function util_make_link ($path, $text, $extra_params=false, $absolute=false) {
	$ep = '' ;
	if (is_array($extra_params)) {
		foreach ($extra_params as $key => $value) {
			$ep .= "$key=\"$value\" ";
		}
	}
	if ($absolute) {
		return '<a ' . $ep . 'href="' . $path . '">' . $text . '</a>' ;
	} else {
		return '<a ' . $ep . 'href="' . util_make_uri($path) . '">' . $text . '</a>' ;
	}
}

/**
 * Create an HTML link to a user's profile page
 * 
 * @param string $username
 * @param int $user_id
 * @param string $text
 * @return string
 */
function util_make_link_u ($username, $user_id,$text) {
	return '<a href="' . util_make_url_u ($username, $user_id) . '">' . $text . '</a>' ;
}

/**
 * Display username with link to a user's profile page
 * and icon face if possible.
 * 
 * @param string $username
 * @param int $user_id
 * @param string $text
 * @param string $size
 * @return string
 */
function util_display_user($username, $user_id,$text, $size='xs') {
        $hook_params = array();
        $hook_params['username'] = $username;
        $hook_params['user_id'] = $user_id;
        $hook_params['user_link'] = '';
        plugin_hook_by_reference("user_link_with_tooltip", $hook_params);
        if($hook_params['user_link'] != ''){
                return $hook_params['user_link'];
        }

        $params = array('user_id' => $user_id, 'size' => $size, 'content' => '');
        plugin_hook_by_reference('user_logo', $params);
        $url = '<a href="' . util_make_url_u ($username, $user_id) . '">' . $text . '</a>';
        if ($params['content']) {
                return $params['content'].$url.'<div class="new_line"></div>';
        }
        return $url;
}

/**
 * Create URL for user's profile page
 * 
 * @param string $username
 * @param int $user_id
 * @return string URL
 */
function util_make_url_u ($username, $user_id) {
	if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
		return util_make_url ("/developer/?user_id=$user_id");
	} else {
		return util_make_url ("/users/$username/");
	}
}

/**
 * Create a HTML link to a project's page
 * @param string $groupame
 * @param int $group_id
 * @param string $text
 * @return string
 */
function util_make_link_g ($groupame, $group_id,$text) {
	return '<a href="' . util_make_url_g ($groupame, $group_id) . '">' . $text . '</a>' ;
}

/**
 * Create URL for a project's page
 * 
 * @param string $groupame
 * @param int $group_id
 * @return string
 */
function util_make_url_g ($groupame, $group_id) {
	if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
		return util_make_url ("/project/?group_id=$group_id");
	} else {
		return util_make_url ("/projects/$groupame/");
	}
}

function util_ensure_value_in_set ($value, $set) {
	if (in_array ($value, $set)) {
		return $value ;
	} else {
		return $set[0] ;
	}
}

function check_email_available($group, $email, &$response) {
	// Check if a mailing list with same name already exists
	$mlFactory = new MailingListFactory($group);
	if (!$mlFactory || !is_object($mlFactory) || $mlFactory->isError()) {
		$response .= $mlFactory->getErrorMessage();
		return false;
	}
	$mlArray = $mlFactory->getMailingLists();
	if ($mlFactory->isError()) {
		$response .= $mlFactory->getErrorMessage();
		return false;
	}
	for ($j = 0; $j < count($mlArray); $j++) {
		$currentList =& $mlArray[$j];
		if ($email == $currentList->getName()) {
			$response .= _('Error: a mailing list with the same email address already exists.');
			return false;
		}
	}
		
	// Check if a forum with same name already exists
	$ff = new ForumFactory($group);
	if (!$ff || !is_object($ff) || $ff->isError()) {
		$response .= $ff->getErrorMessage();
		return false;
	}
	$farr = $ff->getForums();
	$prefix = $group->getUnixName() . '-';
	for ($j = 0; $j < count($farr); $j++) {
		if (is_object($farr[$j])) {
			if ($email == $prefix . $farr[$j]->getName()) {
				$response .= _('Error: a forum with the same email address already exists.');
				return false;
			}
		}
	}
	
	// Email is available
	return true;
}

function use_javascript($js) {
	return $GLOBALS['HTML']->addJavascript($js);
}

function use_stylesheet($css, $media='') {
	return $GLOBALS['HTML']->addStylesheet($css, $media);
}

// array_replace_recursive only appeared in PHP 5.3.0
if (!function_exists('array_replace_recursive')) {
	function array_replace_recursive ($a1, $a2) {
		$result = $a1 ;

		if (!is_array ($a2)) {
			return $a2 ;
		}

		foreach ($a2 as $k => $v) {
			if (!is_array ($v) ||
			    !isset ($result[$k]) || !is_array ($result[$k])) {
				$result[$k] = $v ;
			}
			
			$result[$k] = array_replace_recursive ($result[$k],
							       $v) ;
		}

		return $result ;
	}
}

// json_encode only appeared in PHP 5.2.0
if (!function_exists('json_encode')) {
	require_once $gfcommon.'include/minijson.php' ;
	function json_encode ($a1) {
		return minijson_encode ($a1) ;
	}
}

/* returns an integer from http://forge/foo/bar.php/123 or false */
function util_path_info_last_numeric_component() {
	if (!isset($_SERVER['PATH_INFO']))
		return false;

	$ok = false;
	foreach (str_split($_SERVER['PATH_INFO']) as $x) {
		if ($x == '/') {
			$rv = 0;
			$ok = true;
		} else if ($ok == false) {
			; /* need reset using slash */
		} else if ((ord($x) >= 48) && (ord($x) <= 57)) {
			$rv = $rv * 10 + ord($x) - 48;
		} else {
			$ok = false;
		}
	}
	if ($ok)
		return $rv;
	return false;
}

function get_cvs_binary_version () {
	$string = `cvs --version 2>/dev/null | grep ^Concurrent.Versions.System.*client/server` ;
	if (preg_match ('/^Concurrent Versions System .CVS. 1.11.[0-9]*/', $string)) {
		return '1.11' ;
	} elseif (preg_match ('/^Concurrent Versions System .CVS. 1.12.[0-9]*/', $string)) {
		return '1.12' ;
	} else {
		return '' ;
	}
}

/* get a backtrace as string */
function debug_string_backtrace() {
	ob_start();
	debug_print_backtrace();
	$trace = ob_get_contents();
	ob_end_clean();

	// Remove first item from backtrace as it's this function
	// which is redundant.
	$trace = preg_replace('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '',
	    $trace, 1);

	// Renumber backtrace items.
	$trace = preg_replace('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);

	return $trace;
}

function util_ini_get_bytes($id) {
	$val = trim(ini_get($id));
	$last = strtolower($val[strlen($val)-1]);
	switch ($last) {
	case 'g':
		$val *= 1024;
	case 'm':
		$val *= 1024;
	case 'k':
		$val *= 1024;
	}
	return $val;
}

function util_get_maxuploadfilesize() {
	$postmax = util_ini_get_bytes('post_max_size');
	$maxfile = util_ini_get_bytes('upload_max_filesize');

	$postfile = (int)(($postmax * 3) / 4);

	if ($postfile < $maxfile)
		$postfile = $maxfile;

	return $postfile;
}

/* return $1 if $1 is set, ${2:-false} otherwise */
function util_ifsetor(&$val, $default = false) {
	return (isset($val) ? $val : $default);
}

function util_randbytes($num=6) {
	$f = fopen("/dev/urandom", "rb");
	$b = fread($f, $num);
	fclose($f);

	if (strlen($b) != $num)
		exit_error(_('Internal Error'),
			   _('Could not read from random device'));

	return ($b);
}

/* maximum: 2^31 - 1 due to PHP weakness */
function util_randnum($min=0,$max=32767) {
	$ta = unpack("L", util_randbytes(4));
	$n = $ta[1] & 0x7FFFFFFF;
	$v = $n % (1 + $max - $min);
	return ($min + $v);
}

// sys_get_temp_dir() is only available for PHP >= 5.2.1
if ( !function_exists('sys_get_temp_dir')) {
	function sys_get_temp_dir() {
		if ($temp=getenv('TMP'))    return $temp;
		if ($temp=getenv('TEMP'))   return $temp;
		if ($temp=getenv('TMPDIR')) return $temp;
		return '/tmp';
	}
}

/* convert '\n' to <br /> or </p><p> */
function util_pwrap($encoded_string) {
	return str_replace("<p></p>", "",
	    str_replace("<br /></p>", "</p>",
	    str_replace("<p><br />", "<p>",
	    "<p>" . str_replace("<br /><br />", "</p><p>",
	    implode("<br />", explode("\n",
	    $encoded_string))) . "</p>")));
}
function util_ttwrap($encoded_string) {
	return str_replace("<p><tt></tt></p>", "",
	    str_replace("<br /></tt></p>", "</tt></p>",
	    str_replace("<p><tt><br />", "<p><tt>",
	    "<p><tt>" . str_replace("<br /><br />", "</tt></p><p><tt>",
	    implode("<br />", explode("\n",
	    encoded_string))) . "</tt></p>")));
}

/* takes a string and returns it HTML encoded, URIs made to hrefs */
function util_uri_grabber($unencoded_string, $tryaidtid=false) {
	/* escape all ^A and ^B as ^BX^B and ^BY^B, respectively */
	$s = str_replace("\x01", "\x02X\x02", str_replace("\x02", "\x02Y\x02",
	    $unencoded_string));
	/* replace all URIs with ^AURI^A */
	$s = preg_replace(
	    '|([a-zA-Z][a-zA-Z0-9+.-]*:[#0-9a-zA-Z;/?:@&=+$,_.!~*\'()%-]+)|',
	    "\x01\$1\x01", $s);
	if (!$s)
		return htmlentities($unencoded_string, ENT_QUOTES, "UTF-8");
	/* encode the string */
	$s = htmlentities($s, ENT_QUOTES, "UTF-8");
	/* convert 「^Afoo^A」 to 「<a href="foo">foo</a>」 */
	$s = preg_replace('|\x01([^\x01]+)\x01|',
	    '<a href="$1">$1</a>', $s);
	if (!$s)
		return htmlentities($unencoded_string, ENT_QUOTES, "UTF-8");
//	/* convert [#123] to links if found */
//	if ($tryaidtid)
//		$s = util_tasktracker_links($s);
	/* convert ^BX^B and ^BY^B back to ^A and ^B, respectively */
	$s = str_replace("\x02Y\x02", "\x02", str_replace("\x02X\x02", "\x01",
	    $s));
	/* return the final result */
	return $s;
}

/* secure a (possibly already HTML encoded) string */
function util_html_secure($s) {
	return htmlentities(html_entity_decode($s, ENT_QUOTES, "UTF-8"),
	    ENT_QUOTES, "UTF-8");
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
