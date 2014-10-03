<?php
/**
 * FusionForge miscellaneous utils
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2009-2011, Roland Mas
 * Copyright 2009-2011, Franck Villaume - Capgemini
 * Copyright (c) 2010, 2011, 2012
 *	Thorsten Glaser <t.glaser@tarent.de>
 * Copyright 2010-2012, Alain Peyrat - Alcatel-Lucent
 * Copyright 2013, Franck Villaume - TrivialDev
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
 * htpasswd_apr1_md5 - generate htpasswd md5 format password
 *
 * @param	string	the plain string password
 * @return	string	the apr1 string passwords
 *
 * From http://www.php.net/manual/en/function.crypt.php#73619
 */
function htpasswd_apr1_md5($plainpasswd) {
	$salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
	$len = strlen($plainpasswd);
	$text = $plainpasswd.'$apr1$'.$salt;
	$bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
	$tmp = '';
	for ($i = $len; $i > 0; $i -= 16) {
		$text .= substr($bin, 0, min(16, $i));
	}
	for ($i = $len; $i > 0; $i >>= 1) {
		$text .= ($i & 1)? chr(0) : $plainpasswd{0};
	}
	$bin = pack("H32", md5($text));
	for ($i = 0; $i < 1000; $i++) {
		$new = ($i & 1)? $plainpasswd : $bin;
		if ($i % 3) $new .= $salt;
		if ($i % 7) $new .= $plainpasswd;
		$new .= ($i & 1)? $bin : $plainpasswd;
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
 * is_utf8 - utf-8 detection
 *
 * @param	string	the string to analyze
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
 * util_strip_unprintable - ???
 *
 * @param	$data
 * @return	mixed
 */
function util_strip_unprintable(&$data) {
	if (is_array($data)) {
		foreach ($data as $key => &$value) {
			util_strip_unprintable($value);
		}
	} else {
		$data = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $data);
	}
	return $data;
}

/**
 * removeCRLF - remove any Carriage Return-Line Feed from a string.
 * That function is useful to remove the possibility of a CRLF Injection when sending mail
 * All the data that we will send should be passed through that function
 *
 * @param	string	$str	The string that we want to empty from any CRLF
 * @return	string
 */
function util_remove_CRLF($str) {
	return strtr($str, "\015\012", '  ');
}

/**
 * util_check_fileupload - determines if a filename is appropriate for upload
 *
 * @param	array	$filename	The uploaded file as returned by getUploadedFile()
 * @return	bool
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
	/* ... but we'd rather be paranoid */
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
 * util_check_url - determines if given URL is valid.
 *
 * Currently, test is very basic, only the protocol is
 * checked, allowed values are: http, https, ftp.
 *
 * @param	string	$url	The URL
 * @return	bool	true if valid, false if not valid.
 */
function util_check_url($url) {
	return (preg_match('/^(http|https|ftp):\/\//', $url) > 0);
}

/**
 * util_send_message - Send email
 * This function should be used in place of the PHP mail() function
 *
 * @param	string		$to			The email recipients address
 * @param	string		$subject		The email subject
 * @param	string		$body			The body of the email message
 * @param	string		$from			The optional email sender address.  Defaults to 'noreply@'
 * @param	string		$BCC			The addresses to blind-carbon-copy this message (comma-separated)
 * @param	string		$sendername		The optional email sender name. Defaults to ''
 * @param	bool|string	$extra_headers
 * @param	bool		$send_html_email	Whether to send plain text or html email
 * @param	string		$CC			The addresses to carbon-copy this message (comma-separated)
 */
function util_send_message($to, $subject, $body, $from = '', $BCC = '', $sendername = '', $extra_headers = '',
						   $send_html_email = false, $CC = '') {
	if (!$to) {
		$to = 'noreply@'.forge_get_config('web_host');
	}
	if (!$from) {
		$from = 'noreply@'.forge_get_config('web_host');
	}

	$charset = _('UTF-8');
	if (!$charset) {
		$charset = 'UTF-8';
	}

	$body2 = "Auto-Submitted: auto-generated\n";
	if ($extra_headers) {
		$body2 .= $extra_headers."\n";
	}
	$body2 .= "To: $to".
		"\nFrom: ".util_encode_mailaddr($from, $sendername, $charset);
	if (forge_get_config('bcc_all_emails') != '') {
		$BCC .= ",".forge_get_config('bcc_all_emails');
	}
	if (!empty($BCC)) {
		$body2 .= "\nBCC: $BCC";
	}
	if (!empty($CC)) {
		$body2 .= "\nCC: $CC";
	}
	$send_html_email? $type = "html" : $type = "plain";
	$body2 .= "\n".util_encode_mimeheader("Subject", $subject, $charset).
		"\nContent-type: text/$type; charset=$charset".
		"\n\n".
		util_convert_body($body, $charset);

	if (!forge_get_config('sendmail_path')){
		$sys_sendmail_path="/usr/sbin/sendmail";
	}

	$handle = popen(forge_get_config('sendmail_path')." -f'$from' -t -i", 'w');
	fwrite($handle, $body2);
	pclose($handle);
}

/**
 * util_encode_mailaddr - Encode email address to MIME format
 *
 * @param	string	$email		The email address
 * @param	string	$name		The email's owner name
 * @param	string	$charset	The converting charset
 * @return	string
 */
function util_encode_mailaddr($email, $name, $charset) {
	if (function_exists('mb_convert_encoding') && trim($name) != "") {
		$name = "=?".$charset."?B?".
			base64_encode(mb_convert_encoding(
				$name, $charset, "UTF-8")).
			"?=";
	}

	return $name." <".$email.">";
}

/**
 * util_encode_mimeheader - Encode mimeheader
 *
 * @param	string	$headername	The name of the header (e.g. "Subject")
 * @param	string	$str		The email subject
 * @param	string	$charset	The converting charset (like ISO-2022-JP)
 * @return	string	The MIME encoded subject
 *
 */
function util_encode_mimeheader($headername, $str, $charset) {
	if (function_exists('mb_internal_encoding') &&
	    function_exists('mb_encode_mimeheader')) {
		$x = mb_internal_encoding();
		mb_internal_encoding("UTF-8");
		$y = mb_encode_mimeheader($headername.": ".$str,
			$charset, "Q");
		mb_internal_encoding($x);
		return $y;
	}

	if (!function_exists('mb_convert_encoding')) {
		return $headername.": ".$str;
	}

	return $headername.": "."=?".$charset."?B?".
		base64_encode(mb_convert_encoding(
			$str, $charset, "UTF-8")).
		"?=";
}

/**
 * util_convert_body - Convert body of the email message
 *
 * @param	string	$str		The body of the email message
 * @param	string	$charset	The charset of the email message
 * @return	string	The converted body of the email message
 *
 */
function util_convert_body($str, $charset) {
	if (!function_exists('mb_convert_encoding') || $charset == 'UTF-8') {
		return $str;
	}

	return mb_convert_encoding($str, $charset, "UTF-8");
}

/**
 * util_handle_message - a convenience wrapper which sends messages
 * to an email account
 *
 * @param	array	$id_arr		array of user_id's from the user table
 * @param	string	$subject	subject of the message
 * @param	string	$body		the message body
 * @param	string	$extra_emails	a comma-separated list of email address
 * @param	string	$dummy1		ignored	(no longer used)
 * @param	string	$from		From header
 */
function util_handle_message($id_arr, $subject, $body, $extra_emails = '', $dummy1 = '', $from = '') {
	$address = array();

	if (count($id_arr) < 1) {

	} else {
		$res = db_query_params('SELECT user_id,email FROM users WHERE user_id = ANY ($1)',
					array(db_int_array_to_any_clause($id_arr)));
		$rows = db_numrows($res);

		for ($i = 0; $i < $rows; $i++) {
			if (db_result($res, $i, 'user_id') == 100) {
				// Do not send messages to "Nobody"
				continue;
			}
			$address['email'][] = db_result($res,$i,'email');
		}
		if (isset ($address['email']) && count($address['email']) > 0) {
			$extra_emails = implode($address['email'], ',').','.$extra_emails;
		}
	}
	if ($extra_emails) {
		util_send_message('', $subject, $body, $from, $extra_emails);
	}
}

/**
 * util_unconvert_htmlspecialchars - Unconverts a string converted with htmlspecialchars()
 *
 * @param	string	$string	The string to unconvert
 * @return	string	The unconverted string
 *
 */
function util_unconvert_htmlspecialchars($string) {
	return html_entity_decode($string, ENT_QUOTES, "UTF-8");
}

/**
 * util_result_columns_to_assoc - Takes a result set and turns the column pair into an associative array
 *
 * @param	string	$result		The result set ID
 * @param	int	$col_key	The column key
 * @param	int	$col_val	The optional column value
 * @return	array			An associative array
 *
 */
function util_result_columns_to_assoc($result, $col_key = 0, $col_val = 1) {
	$rows = db_numrows($result);

	if ($rows > 0) {
		$arr = array();
		for ($i = 0; $i < $rows; $i++) {
			$arr[db_result($result, $i, $col_key)] = db_result($result, $i, $col_val);
		}
	} else {
		$arr = array();
	}
	return $arr;
}

/**
 * util_result_column_to_array - Takes a result set and turns the optional column into an array
 *
 * @param	int	$result	The result set ID
 * @param	int	$col	The column
 * @return	array
 *
 */
function &util_result_column_to_array($result, $col = 0) {
	/*
		Takes a result set and turns the optional column into
		an array
	*/
	$rows = db_numrows($result);

	if ($rows > 0) {
		$arr = array();
		for ($i = 0; $i < $rows; $i++) {
			$arr[$i] = db_result($result, $i, $col);
		}
	} else {
		$arr = array();
	}
	return $arr;
}

/**
 * util_line_wrap - Automatically linewrap text
 *
 * @param	string	$text	The text to wrap
 * @param	int	$wrap	The number of characters to wrap - Default is 80
 * @param	string	$break	The line break to use - Default is '\n'
 * @return	string	The wrapped text
 *
 */
function util_line_wrap($text, $wrap = 80, $break = "\n") {
	return wordwrap($text, $wrap, $break, false);
}

/**
 * util_make_links - Turn URL's into HREF's.
 *
 * @param	string	$data	The URL
 * @return	mixed|string	The HREF'ed URL
 *
 */
function util_make_links($data = '') {
	if (empty($data)) {
		return $data;
	}
	$withPattern = 0;
	for ($i = 0; $i < 5; $i++) {
		$randPattern = rand(10000, 30000);
		if (!preg_match("/$randPattern/", $data)) {
			$withPattern = 1;
			break;
		}
	}
	if ($withPattern) {
/*
		while(preg_match('/<a [^>]*>[^<]*<\/a>/i', $data, $part)) {
			$mem[] = $part[0];
			$data = preg_replace('/<a [^>]*>[^<]*<\/a>/i', $randPattern, $data, 1);
		}
*/
		$mem = array();
		while (preg_match('/<a [^>]*>.*<\/a>/siU', $data, $part)) {
			$mem[] = $part[0];
			$data = preg_replace('/<a [^>]*>.*<\/a>/siU', $randPattern, $data, 1);
		}
		while (preg_match('/<img [^>]*\/>/siU', $data, $part)) {
			$mem[] = $part[0];
			$data = preg_replace('/<img [^>]*\/>/siU', $randPattern, $data, 1);
		}
		$data = str_replace('&gt;', "\1", $data);
		$data = preg_replace("#([ \t]|^)www\.#i", " http://www.", $data);
		$data = preg_replace("#([[:alnum:]]+)://([^[:space:]<\1]*)([[:alnum:]\#?/&=])#i", "<a href=\"\\1://\\2\\3\" target=\"_new\">\\1://\\2\\3</a>", $data);
		$data = preg_replace("#([[:space:]]|^)(([a-z0-9_]|\\-|\\.)+@([^[:space:]<\1]*)([[:alnum:]-]))#i", "\\1<a href=\"mailto:\\2\" target=\"_new\">\\2</a>", $data);
		$data = str_replace("\1", '&gt;', $data);
		for ($i = 0; $i < count($mem); $i++) {
			$data = preg_replace("/$randPattern/", $mem[$i], $data, 1);
		}
		return ($data);
	}

	$lines = split("\n", $data);
	$newText = "";
	while (list ($key, $line) = each($lines)) {
		// Do not scan lines if they already have hyperlinks.
		// Avoid problem with text written with an WYSIWYG HTML editor.
		if (eregi('<a ([^>]*)>.*</a>', $line, $linePart)) {
			if (eregi('href="[^"]*"', $linePart[1])) {
				$newText .= $line;
				continue;
			}
		}

		// Skip </img> tag also
		if (eregi('<img ([^>]*)/>', $line, $linePart)) {
			if (eregi('href="[^"]*"', $linePart[1])) {
				$newText .= $line;
				continue;
			}
		}

		// When we come here, we usually have form input
		// encoded in entities. Our aim is to NOT include
		// angle brackets in the URL
		// (RFC2396; http://www.w3.org/Addressing/URL/5.1_Wrappers.html)
		$line = str_replace('&gt;', "\1", $line);
		$line = preg_replace("/([ \t]|^)www\./i", " http://www.", $line);
		$line = preg_replace("/([[:alnum:]]+):\/\/([^[:space:]<\1]*)([[:alnum:]#?\/&=])/i",
			"<a href=\"\\1://\\2\\3\" target=\"_new\">\\1://\\2\\3</a>", $line);
		$line = preg_replace(
			"/([[:space:]]|^)(([a-z0-9_]|\\-|\\.)+@([^[:space:]]*)([[:alnum:]-]))/i",
			"\\1<a href=\"mailto:\\2\" target=\"_new\">\\2</a>",
			$line
		);
		$line = str_replace("\1", '&gt;', $line);
		$newText .= $line;
	}
	return $newText;
}

/**
 * show_priority_colors_key - Show the priority colors legend
 *
 * @return	string	html code
 *
 */
function show_priority_colors_key() {
	echo '<p><strong> '._('Priority Colors')._(':').'</strong>';
	for ($i = 1; $i < 6; $i++) {
		echo ' <span class="priority'.$i.'">'.$i.'</span>';
	}
	echo '</p>';
}

/**
 * utils_buildcheckboxarray - Build a checkbox array
 *
 * @param	int	$options	Number of options to be in the array
 * @param	string	$name		The name of the checkboxes
 * @param	array	$checked_array	An array of boxes to be pre-checked
 *
 */
function utils_buildcheckboxarray($options, $name, $checked_array) {
	$option_count = count($options);
	$checked_count = count($checked_array);

	for ($i = 1; $i <= $option_count; $i++) {
		echo '
			<br /><input type="checkbox" name="'.$name.'" value="'.$i.'"';
		for ($j = 0; $j < $checked_count; $j++) {
			if ($i == $checked_array[$j]) {
				echo ' checked';
			}
		}
		echo ' /> '.$options[$i];
	}
}

/**
 * utils_requiredField - Adds the required field marker
 *
 * @return	string	A string holding the HTML to mark a required field
 */
function utils_requiredField() {
	return '<span class="requiredfield">*</span>';
}

/**
 * GraphResult - Takes a database result set and builds a graph.
 * The first column should be the name, and the second column should be the values
 * Be sure to include HTL_Graphs.php before using this function
 *
 * @author	Tim Perdue tperdue@valinux.com
 * @param	int	$result	The databse result set ID
 * @param	string	$title	The title of the graph
 *
 */
function GraphResult($result, $title) {
	$rows = db_numrows($result);

	if ((!$result) || ($rows < 1)) {
		echo 'None Found.';
	} else {
		$names = array();
		$values = array();

		for ($j = 0; $j < db_numrows($result); $j++) {
			if (db_result($result, $j, 0) != '' && db_result($result, $j, 1) != '') {
				$names[$j] = db_result($result, $j, 0);
				$values[$j] = db_result($result, $j, 1);
			}
		}

	/*
		This is another function detailed below
	*/
		GraphIt($names, $values, $title);
	}
}

/**
 * GraphIt() - Build a graph
 *
 * @author	Tim Perdue tperdue@valinux.com
 * @param	array	$name_string	An array of names
 * @param	array	$value_string	An array of values
 * @param	string	$title		The title of the graph
 * @return	string	html code
 *
 */
function GraphIt($name_string, $value_string, $title) {
	global $HTML;

	$counter = count($name_string);

	/*
		Can choose any color you wish
	*/
	$bars = array();

	for ($i = 0; $i < $counter; $i++) {
		$bars[$i] = $HTML->COLOR_LTBACK1;
	}

	$counter = count($value_string);

	/*
		Figure the max_value passed in, so scale can be determined
	*/

	$max_value = 0;

	for ($i = 0; $i < $counter; $i++) {
		if ($value_string[$i] > $max_value) {
			$max_value = $value_string[$i];
		}
	}

	if ($max_value < 1) {
		$max_value = 1;
	}

	/*
		I want my graphs all to be 800 pixels wide, so that is my divisor
	*/

	$scale = (400/$max_value);

	/*
		I create a wrapper table around the graph that holds the title
	*/

	$title_arr = array();
	$title_arr[] = $title;

	echo $GLOBALS['HTML']->listTableTop($title_arr);
	echo '<tr><td>';
	/*
		Create an associate array to pass in. I leave most of it blank
	*/

	$vals = array(
		'vlabel'      => '',
		'hlabel'      => '',
		'type'        => '',
		'cellpadding' => '',
		'cellspacing' => '0',
		'border'      => '',
		'width'       => '',
		'background'  => '',
		'vfcolor'     => '',
		'hfcolor'     => '',
		'vbgcolor'    => '',
		'hbgcolor'    => '',
		'vfstyle'     => '',
		'hfstyle'     => '',
		'noshowvals'  => '',
		'scale'       => $scale,
		'namebgcolor' => '',
		'valuebgcolor'=> '',
		'namefcolor'  => '',
		'valuefcolor' => '',
		'namefstyle'  => '',
		'valuefstyle' => '',
		'doublefcolor'=> '');

	/*
		This is the actual call to the HTML_Graphs class
	*/

	html_graph($name_string, $value_string, $bars, $vals);

	echo '
		</td></tr>
		<!-- end outer graph table -->';
	echo $GLOBALS['HTML']->listTableBottom();
}

/**
 * ShowResultSet - Show a generic result set
 * Very simple, plain way to show a generic result set
 *
 * @param	int	$result			The result set ID
 * @param	string	$title			The title of the result set
 * @param	bool	$linkify		The option to turn URL's into links
 * @param	bool	$displayHeaders		The option to display headers
 * @param	array	$headerMapping		The db field name -> label mapping
 * @param	array	$excludedCols		Don't display these cols
 */
function ShowResultSet($result, $title = '', $linkify = false, $displayHeaders = true, $headerMapping = array(), $excludedCols = array()) {
	global $group_id, $HTML;

	if ($result) {
		$rows = db_numrows($result);
		$cols = db_numfields($result);

		echo '<table class="fullwidth">'."\n";

		/*  Create  the  headers  */
		$headersCellData = array();
		$colsToKeep = array();
		for ($i = 0; $i < $cols; $i++) {
			$fieldName = db_fieldname($result, $i);
			if (in_array($fieldName, $excludedCols)) {
				continue;
			}
			$colsToKeep[] = $i;
			if (isset($headerMapping[$fieldName])) {
				if (is_array($headerMapping[$fieldName])) {
					$headersCellData[] = $headerMapping[$fieldName];
				} else {
					$headersCellData[] = array($headerMapping[$fieldName]);
				}
			} else {
				$headersCellData[] = array($fieldName);
			}
		}

		/*  Create the title  */
		if (strlen($title) > 0) {
			$titleCellData = array();
			$titleCellData[] = array($title, 'colspan="'.count($headersCellData).'"');
			echo $HTML->multiTableRow('', $titleCellData, TRUE);
		}

		/* Display the headers */
		if ($displayHeaders) {
			echo $HTML->multiTableRow('', $headersCellData, TRUE);
		}

		/*  Create the rows  */
		for ($j = 0; $j < $rows; $j++) {
			echo '<tr '.$HTML->boxGetAltRowStyle($j).'>';
			for ($i = 0; $i < $cols; $i++) {
				if (in_array($i, $colsToKeep)) {
					if ($linkify && $i == 0) {
						$link = '<a href="'.getStringFromServer('PHP_SELF').'?';
						$linkend = '</a>';
						if ($linkify == "bug_cat") {
							$link .= 'group_id='.$group_id.'&amp;bug_cat_mod=y&amp;bug_cat_id='.db_result($result, $j, 'bug_category_id').'">';
						} elseif ($linkify == "bug_group") {
							$link .= 'group_id='.$group_id.'&amp;bug_group_mod=y&amp;bug_group_id='.db_result($result, $j, 'bug_group_id').'">';
						} elseif ($linkify == "patch_cat") {
							$link .= 'group_id='.$group_id.'&amp;patch_cat_mod=y&amp;patch_cat_id='.db_result($result, $j, 'patch_category_id').'">';
						} elseif ($linkify == "support_cat") {
							$link .= 'group_id='.$group_id.'&amp;support_cat_mod=y&amp;support_cat_id='.db_result($result, $j, 'support_category_id').'">';
						} elseif ($linkify == "pm_project") {
							$link .= 'group_id='.$group_id.'&amp;project_cat_mod=y&amp;project_cat_id='.db_result($result, $j, 'group_project_id').'">';
						} else {
							$link = $linkend = '';
						}
					} else {
						$link = $linkend = '';
					}
					echo '<td>'.$link.db_result($result, $j, $i).$linkend.'</td>';
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
 * validate_email - Validate an email address
 *
 * @param	string	$address	The address string to validate
 * @return	bool	true on success/false on error
 *
 */
function validate_email($address) {
	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
		if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
			return true;
		} else {
			return false;
		}
	} else {
		if (preg_match("/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+@[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+$/", $address)) {
			return true;
		} else {
			return false;
		}
	}
}

/**
 * validate_emails - Validate a list of e-mail addresses
 *
 * @param	string	$addresses	E-mail list
 * @param	string	$separator	Separator
 * @return	array	Array of invalid e-mail addresses (if empty, all addresses are OK)
 */
function validate_emails($addresses, $separator = ',') {
	if (strlen($addresses) == 0) return array();

	$emails = explode($separator, $addresses);
	$ret = array();

	if (is_array($emails)) {
		foreach ($emails as $email) {
			$email = trim($email); // This is done so we can validate lists like "a@b.com, c@d.com"
			if (!validate_email($email)) $ret[] = $email;
		}
	}
	return $ret;
}

/**
 * util_is_valid_filename - Verifies whether a file has a valid filename
 *
 * @param	string	$file	The file to verify
 * @return	bool	true on success/false on error
 *
 */
function util_is_valid_filename($file) {
	//bad char test
	$invalidchars = preg_replace("/[-A-Z0-9+_\. ~]/i", "", $file);

	if (!empty($invalidchars)) {
		return false;
	} else {
		if (strstr($file, '..')) {
			return false;
		} else {
			return true;
		}
	}
}

/**
 * util_is_valid_repository_name - Verifies whether a repository name is valid
 *
 * @param	string	$file	name to verify
 * @return	bool	true on success/false on error
 *
 */
function util_is_valid_repository_name ($file) {
	//bad char test
	$invalidchars = preg_replace("/[-A-Z0-9+_\.]/i","",$file);

	if (!empty($invalidchars)) {
		return false;
	}
	if (strstr($file,'..')) {
		return false;
	}
	return true;
}

/**
 * valid_hostname - Validates a hostname string to make sure it doesn't contain invalid characters
 *
 * @param	string	$hostname The optional hostname string
 * @return	bool	true on success/false on failure
 *
 */
function valid_hostname($hostname = "xyz") {

	//bad char test
	$invalidchars = preg_replace("/[-A-Z0-9\.]/i", "", $hostname);

	if (!empty($invalidchars)) {
		return false;
	}

	//double dot, starts with a . or -
	if (preg_match("/\.\./", $hostname) || preg_match("/^\./", $hostname) || preg_match("/^\-/", $hostname)) {
		return false;
	}

	$multipoint = explode(".", $hostname);

	if (!(is_array($multipoint)) || ((count($multipoint) - 1) < 1)) {
		return false;
	}

	return true;

}


/**
 * human_readable_bytes - Translates an integer representing bytes to a human-readable format.
 *
 * Format file size in a human-readable way
 * such as "xx Megabytes" or "xx Mo"
 *
 * @author	Andrea Paleni <andreaSPAMLESS_AT_SPAMLESScriticalbit.com>
 * @version	1.0
 *
 * @param	int	$bytes	is the size
 * @param	bool	$base10	enable base 10 representation, otherwise default base 2  is used
 * @param	int	$round	number of fractional digits
 * @param	array	$labels	strings associated to each 2^10 or 10^3(base10==true) multiple of base units
 * @return	string
 */
function human_readable_bytes($bytes, $base10 = false, $round = 0, $labels = array()) {
	if ($bytes == 0) {
		return "0";
	}
	if ($bytes < 0) {
		return "-".human_readable_bytes(-$bytes, $base10, $round);
	}
	if ($base10) {
		$labels = array(_('bytes'), _('kB'), _('MB'), _('GB'), _('TB'));
		$step = 3;
		$base = 10;
	} else {
		$labels = array(_('bytes'), _('KiB'), _('MiB'), _('GiB'), _('TiB'));
		$step = 10;
		$base = 2;
	}
	$log = (int)(log10($bytes)/log10($base));
	krsort($labels);
	foreach ($labels as $p => $lab) {
		$pow = $p * $step;
		if ($log < $pow) {
			continue;
		}
		if ($lab != _("bytes") and $lab != _("kB") and $lab != _("KiB")) {
			$round = 2;
		}
		$text = round($bytes/pow($base, $pow), $round)." ".$lab;
		break;
	}
	return $text;
}

/**
 * ls - lists a specified directory and returns an array of files
 * @param	string	$dir	the path of the directory to list
 * @param	bool	$filter	whether to filter out directories and illegal filenames
 * @return	array	array of file names.
 */
function &ls($dir, $filter = false) {
	$out = array();

	if (is_dir($dir) && ($h = opendir($dir))) {
		while (($f = readdir($h)) !== false) {
			if ($f[0] == '.')
				continue;
			if ($filter) {
				if (!util_is_valid_filename($f) ||
					!is_file($dir."/".$f)
				)
					continue;
			}
			$out[] = $f;
		}
		closedir($h);
	}
	return $out;
}

/**
 * readfile_chunked - replacement for readfile
 *
 * @param	string	$filename	The file path
 * @param	bool	$returnBytes	Whether to return bytes served or just a bool
 * @return	bool|int
 */
function readfile_chunked($filename, $returnBytes = true) {
	$chunksize = 1*(1024*1024); // 1MB chunks
	$buffer = '';
	$byteCounter = 0;

	$handle = fopen($filename, 'rb');
	if ($handle === false) {
		return false;
	}

	ob_start();
	while (!feof($handle)) {
		$buffer = fread($handle, $chunksize);
		echo $buffer;
		ob_flush();
		flush();
		if ($returnBytes) {
			$byteCounter += strlen($buffer);
		}
	}
	ob_end_flush();
	$status = fclose($handle);
	if ($returnBytes && $status) {
		return $byteCounter; // return num. bytes delivered like readfile() does.
	}
	return $status;
}

/**
 * util_is_root_dir - Checks if a directory points to the root dir
 *
 * @param	string	$dir	Directory
 * @return	bool
 */
function util_is_root_dir($dir) {
	return !preg_match('/[^\\/]/', $dir);
}

/**
 * util_is_dot_or_dotdot - Checks if a directory points to . or ..
 *
 * @param	string	$dir	Directory
 * @return	bool
 */
function util_is_dot_or_dotdot($dir) {
	return preg_match('/^\.\.?$/', trim($dir, '/'));
}

/**
 * util_containts_dot_or_dotdot - Checks if a directory containts . or ..
 *
 * @param	string	$dir	Directory
 * @return	bool
 */
function util_containts_dot_or_dotdot($dir) {
	foreach (explode('/', $dir) as $sub_dir) {
		if (util_is_dot_or_dotdot($sub_dir))
			return true;
	}

	return false;
}

/**
 * util_secure_filename - Returns a secured file name
 *
 * @param	string	$file	Filename
 * @return	string	Filename
 */
function util_secure_filename($file) {
	$f = preg_replace("/[^-A-Z0-9_\.]/i", '', $file);
	if (util_containts_dot_or_dotdot($f))
		$f = preg_replace("/\./", '_', $f);
	if (!$f)
		$f = md5($file);
	return $f;
}

/**
 * util_strip_accents - Remove accents from given text.
 *
 * @param	string	$text Text
 * @return	string
 */
function util_strip_accents($text) {
	$find = utf8_decode($text);
	$find = strtr($find,
		utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'),
			'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
	return utf8_encode($find);
}

/**
 * normalized_urlprefix - Constructs the forge's URL prefix out of forge_get_config('url_prefix')
 *
 * @return	string
 */
function normalized_urlprefix() {
	$prefix = forge_get_config('url_prefix');
	$prefix = preg_replace("/^\//", "", $prefix);
	$prefix = preg_replace("/\/$/", "", $prefix);
	$prefix = "/$prefix/";
	if ($prefix == '//')
		$prefix = '/';
	return $prefix;
}

/**
 * util_url_prefix - Return URL prefix (http:// or https://)
 *
 * @param	string	$prefix (optional) : 'http' or 'https' to force it
 * @return	string	URL prefix
 */
function util_url_prefix($prefix = '') {
	if ($prefix == 'http' || $prefix == 'https' ) {
		return $prefix . '://';
	}
	else {
		if (forge_get_config('use_ssl')) {
			return "https://";
		} else {
			return "http://";
		}
	}
}

/**
 * util_make_base_url - Construct the base URL http[s]://forge_name[:port]
 *
 * @param	string	$prefix (optional) : 'http' or 'https' to force it
 * @return	string	base URL
 */
function util_make_base_url($prefix = '') {
	$url = util_url_prefix($prefix);
	$url .= forge_get_config('web_host');
	if (forge_get_config('https_port') && (forge_get_config('https_port') != 443)) {
		$url .= ":".forge_get_config('https_port');
	}
	return $url;
}

/**
 * util_make_url - Construct full URL from a relative path
 *
 * @param	string	$path (optional)
 * @param       string  $prefix (optional) : 'http' or 'https' to force it
 * @return	string	URL
 */
function util_make_url($path = '', $prefix = '') {
	$url = util_make_base_url($prefix).util_make_uri($path) ;
	return $url;
}

/**
 * util_find_relative_referer - Find the relative URL from full URL, removing http[s]://forge_name[:port]
 *
 * @param	string	$url	URL
 * @return	string
 */
function util_find_relative_referer($url) {
	$relative_url = str_replace(util_make_base_url(), '', $url);
	//now remove previous feedback, error_msg or warning_msg
	$relative_url = preg_replace('/&error_msg=.*&/', '&', $relative_url);
	$relative_url = preg_replace('/&warning_msg=.*&/', '&', $relative_url);
	$relative_url = preg_replace('/&feedback=.*&/', '&', $relative_url);
	$relative_url = preg_replace('/&error_msg=.*/', '', $relative_url);
	$relative_url = preg_replace('/&warning_msg=.*/', '', $relative_url);
	$relative_url = preg_replace('/&feedback=.*/', '', $relative_url);
	return $relative_url;
}

/**
 * util_make_uri - Construct proper (relative) URI (prepending prefix)
 *
 * @param	string	$path
 * @return	string URI
 */
function util_make_uri($path) {
	$path = preg_replace('/^\//', '', $path);
	$uri = normalized_urlprefix();
	$uri .= $path;
	return $uri;
}

/**
 * util_make_link - Construct proper (relative) URI from path & text
 *
 * @param	string		$path
 * @param	string		$text
 * @param	array|bool	$extra_params
 * @param	bool		$absolute
 * @return	string URI
 */
function util_make_link($path, $text, $extra_params = false, $absolute = false) {
	global $use_tooltips;
	$ep = '';
	if (is_array($extra_params)) {
		foreach ($extra_params as $key => $value) {
			if ($key != 'title') {
				$ep .= "$key=\"$value\" ";
			}
			if ($key == 'title' && $use_tooltips) {
				$ep .= "$key=\"$value\" ";
			}
		}
	}
	if ($absolute) {
		return '<a '.$ep.'href="'.$path.'">'.$text.'</a>';
	} else {
		return '<a '.$ep.'href="'.util_make_uri($path).'">'.$text.'</a>';
	}
}

/**
 * util_make_link_u - Create an HTML link to a user's profile page
 *
 * @param	string	$username
 * @param	int	$user_id
 * @param	string	$text
 * @return	string
 */
function util_make_link_u($username, $user_id, $text) {
	return '<a href="'.util_make_url_u($username, $user_id).'">'.$text.'</a>';
}

/**
 * util_display_user - Display username with link to a user's profile page
 * 			and icon face if possible.
 *
 * @param	string	$username
 * @param	int	$user_id
 * @param	string	$text
 * @param	string	$size
 * @return	string
 */
function util_display_user($username, $user_id, $text, $size = 'xs') {
	// Invoke user_link_with_tooltip plugin
	$hook_params = array('resource_type' => 'user', 'username' => $username, 'user_id' => $user_id, 'size' => $size, 'user_link' => '');
	plugin_hook_by_reference('user_link_with_tooltip', $hook_params);
	if ($hook_params['user_link'] != '') {
		return $hook_params['user_link'];
	}

	// If no plugin replaced it, then back to default standard link

	// Invoke user_logo plugin (see gravatar plugin for instance)
	$params = array('user_id' => $user_id, 'size' => $size, 'content' => '');
	plugin_hook_by_reference('user_logo', $params);

	$url = '<a href="'.util_make_url_u($username, $user_id).'">'.$text.'</a>';
	if ($params['content']) {
		return $params['content'].$url.'<div class="new_line"></div>';
	}
	return $url;
}

/**
 * util_make_url_u - Create URL for user's profile page
 *
 * @param	string	$username
 * @param	int	$user_id
 * @return	string URL
 */
function util_make_url_u($username, $user_id) {
	if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
		return util_make_url("/developer/?user_id=$user_id");
	} else {
		return util_make_url("/users/$username/");
	}
}

/**
 * util_make_link_g - Create a HTML link to a project's page
 *
 * @param	string	$group_name
 * @param	int	$group_id
 * @param	string	$text
 * @return	string
 */
function util_make_link_g($group_name, $group_id, $text) {
	$hook_params = array();
	$hook_params['resource_type'] = 'group';
	$hook_params['group_name'] = $group_name;
	$hook_params['group_id'] = $group_id;
	$hook_params['link_text'] = $text;
	$hook_params['group_link'] = '';
	plugin_hook_by_reference('project_link_with_tooltip', $hook_params);
	if ($hook_params['group_link'] != '') {
		return $hook_params['group_link'];
	}

	return '<a href="'.util_make_url_g($group_name, $group_id).'">'.$text.'</a>';
}

/**
 * util_make_url_g - Create URL for a project's page
 *
 * @param	string	$group_name
 * @param	int	$group_id
 * @return	string
 */
function util_make_url_g($group_name, $group_id) {
	if (isset ($GLOBALS['sys_noforcetype']) && $GLOBALS['sys_noforcetype']) {
		return util_make_url("/project/?group_id=$group_id");
	} else {
		return util_make_url("/projects/$group_name/");
	}
}

function util_ensure_value_in_set($value, $set) {
	if (in_array($value, $set)) {
		return $value;
	} else {
		return $set[0];
	}
}

/**
 * check_email_available - ???
 *
 * @param	Group	$group
 * @param	string	$email
 * @param	string	$response
 * @return	bool
 */
function check_email_available($group, $email, &$response) {
	// Check if a mailing list with same name already exists
	if ($group->usesMail()) {
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
	}

	// Check if a forum with same name already exists
	if ($group->usesForum()) {
		$ff = new ForumFactory($group);
		if (!$ff || !is_object($ff) || $ff->isError()) {
			$response .= $ff->getErrorMessage();
			return false;
		}
		$farr = $ff->getForums();
		$prefix = $group->getUnixName().'-';
		for ($j = 0; $j < count($farr); $j++) {
			if (is_object($farr[$j])) {
				if ($email == $prefix.$farr[$j]->getName()) {
					$response .= _('Error: a forum with the same email address already exists.');
					return false;
				}
			}
		}
	}

	// Email is available
	return true;
}

/**
 * Adds the Javascript file to the list to be used
 * @param string $js
 */
function use_javascript($js) {
	return $GLOBALS['HTML']->addJavascript($js);
}

function use_stylesheet($css, $media = '') {
	return $GLOBALS['HTML']->addStylesheet($css, $media);
}

// array_replace_recursive only appeared in PHP 5.3.0
if (!function_exists('array_replace_recursive')) {
	/**
	 * Replaces elements from passed arrays into the first array recursively
	 * @param array $a1 The array in which elements are replaced.
	 * @param array $a2 The array from which elements will be extracted.
	 * @return array Returns an array, or NULL if an error occurs.
	 */
	function array_replace_recursive($a1, $a2) {
		$result = $a1;

		if (!is_array($a2)) {
			return $a2;
		}

		foreach ($a2 as $k => $v) {
			if (!is_array($v) ||
				!isset($result[$k]) || !is_array($result[$k])) {
				$result[$k] = $v;
			}

			$result[$k] = array_replace_recursive($result[$k], $v);
		}

		return $result;
	}
}

// json_encode only appeared in PHP 5.2.0
if (!function_exists('json_encode')) {
	require_once $gfcommon.'include/minijson.php';
	function json_encode($a1) {
		return minijson_encode($a1);
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
		} elseif ($ok == false) {
			; /* need reset using slash */
		} elseif ((ord($x) >= 48) && (ord($x) <= 57)) {
			$rv = $rv * 10 + ord($x) - 48;
		} else {
			$ok = false;
		}
	}
	if ($ok)
		return $rv;
	return false;
}

function get_cvs_binary_version() {
	$string = `cvs --version 2>/dev/null | grep ^Concurrent.Versions.System.*client/server`;
	if (preg_match('/^Concurrent Versions System .CVS. 1.11.[0-9]*/', $string)) {
		return '1.11';
	} elseif (preg_match('/^Concurrent Versions System .CVS. 1.12.[0-9]*/', $string)) {
		return '1.12';
	} else {
		return '';
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
	$trace = preg_replace('/^#0\s+'.__FUNCTION__."[^\n]*\n/", '',
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

	return min($postmax, $maxfile);
}

function util_get_compressed_file_extension() {
	$m = forge_get_config('compression_method');
	if (preg_match('/^gzip\b/', $m)) {
		return '.gz';
	} elseif (preg_match('/^bzip2\b/', $m)) {
		return '.bzip2';
	} elseif (preg_match('/^lzma\b/', $m)) {
		return '.lzma';
	} elseif (preg_match('/^xz\b/', $m)) {
		return '.xz';
	} elseif (preg_match('/^cat\b/', $m)) {
		return '';
	} else {
		return '.compressed';
	}
}

/**
 * return $1 if $1 is set, ${2:-false} otherwise
 *
 * Shortcomings: may create $$val = NULL in the
 * current namespace; see the (rejected – but
 * then, with PHP, you know where you stand…)
 * https://wiki.php.net/rfc/ifsetor#userland_2
 * proposal for details and a (rejected) fix.
 *
 * Do not use this function if $val is “magic”,
 * for example, an overloaded \ArrayAccess.
 */
function util_ifsetor(&$val, $default = false) {
	return (isset($val) ? $val : $default);
}

function util_randbytes($num = 6) {
	$b = '';

	// Let's try /dev/urandom first
	$f = @fopen("/dev/urandom", "rb");
	if ($f !== FALSE) {
		$b .= @fread($f, $num);
		fclose($f);
	}

	// Hm.  No /dev/urandom?  Try /dev/random.
	if (strlen($b) < $num) {
		$f = @fopen("/dev/random", "rb");
		if ($f !== FALSE) {
			$b .= @fread($f, $num);
			fclose($f);
		}
	}

	// Still no luck?  Fall back to PHP's built-in PRNG
	while (strlen($b) < $num) {
		$b .= uniqid(mt_rand(), true);
	}

	$b = substr($b, 0, $num);
	return ($b);
}

/* maximum: 2^31 - 1 due to PHP weakness */
function util_randnum($min = 0, $max = 32767) {
	$ta = unpack("L", util_randbytes(4));
	$n = $ta[1] & 0x7FFFFFFF;
	$v = $n % (1 + $max - $min);
	return ($min + $v);
}

// sys_get_temp_dir() is only available for PHP >= 5.2.1
if (!function_exists('sys_get_temp_dir')) {
	function sys_get_temp_dir() {
		if ($temp = getenv('TMP')) return $temp;
		if ($temp = getenv('TEMP')) return $temp;
		if ($temp = getenv('TMPDIR')) return $temp;
		return '/tmp';
	}
}

/* convert '\n' to <br /> or </p><p> */
function util_pwrap($encoded_string) {
	return str_replace("<p></p>", "",
		str_replace("<br /></p>", "</p>",
			str_replace("<p><br />", "<p>",
				"<p>".str_replace("<br /><br />", "</p><p>",
					implode("<br />", explode("\n",
						$encoded_string)))."</p>")));
}

/* takes a string and returns it HTML encoded, URIs made to hrefs */
function util_uri_grabber($unencoded_string, $tryaidtid = false) {
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

function util_html_encode($s) {
	return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

/* secure a (possibly already HTML encoded) string */
function util_html_secure($s) {
	return util_html_encode(util_unconvert_htmlspecialchars($s));
}

/* return integral value (ℕ₀) of passed string if it matches, or false */
function util_nat0(&$s) {
	if (!isset($s)) {
		/* unset variable */
		return false;
	}
	if (is_array($s)) {
		if (count($s) == 1) {
			/* one-element array */
			return util_nat0($s[0]);
		}
		/* not one element, or element not at [0] */
		return false;
	}
	if (!is_numeric($s)) {
		/* not numeric */
		return false;
	}
	$num = (int)$s;
	if ($num >= 0) {
		/* number element of ℕ₀ */
		$text = (string)$num;
		if ($text == $s) {
			/* number matches its textual representation */
			return ($num);
		}
		/* doesn't match, like 0123 or 1.2 or " 1" */
	}
	/* or negative */
	return false;
}

/**
 * util_negociate_alternate_content_types() - Manage content-type negociation based on 'script_accepted_types' hooks
 * @param string		$script
 * @param string		$default_content_type
 * @param string|bool	$forced_content_type
 * @return string
 */
function util_negociate_alternate_content_types($script, $default_content_type, $forced_content_type=false) {

	$content_type = $default_content_type;

	// we can force the content-type to be returned automatically if necessary
	if ($forced_content_type) {
		// TODO ideally, in this case we could try and apply the negociation to see if it matches
		// one provided by the hooks, but negotiateMimeType() doesn't allow this so for the moment,
		// we just force it whatever the hooks support
		$content_type = $forced_content_type;
	} else {
		// Invoke plugins' hooks 'script_accepted_types' to discover which alternate content types they would accept for /users/...
		$hook_params = array();
		$hook_params['script'] = $script;
		$hook_params['accepted_types'] = array();

		plugin_hook_by_reference('script_accepted_types', $hook_params);

		if (count($hook_params['accepted_types'])) {
			// By default, text/html is accepted
			$accepted_types = array($default_content_type);
			$new_accepted_types = $hook_params['accepted_types'];
			$accepted_types = array_merge($accepted_types, $new_accepted_types);

			// PEAR::HTTP (for negotiateMimeType())
			require_once 'HTTP.php';

			// negociate accepted content-type depending on the preferred ones declared by client
			$http = new HTTP();
			$content_type = $http->negotiateMimeType($accepted_types, false);
		}
	}
	return $content_type;
}

/**
 * util_gethref() - Construct a hypertext reference
 *
 * @param	string	$baseurl
 *			(optional) base URL (absolute or relative);
 *			urlencoded, but not htmlencoded
 *			(default (falsy): PHP_SELF)
 * @param	array	$args
 *			(optional) associative array of unencoded query parameters;
 *			false values are ignored
 * @param	bool	$ashtml
 *			(optional) htmlencode the result?
 *			(default: true)
 * @param	string	$sep
 *			(optional) argument separator ('&' or ';')
 *			(default: '&')
 * @return	string	URL, possibly htmlencoded
 */
function util_gethref($baseurl = '', $args = array(), $ashtml = true, $sep = '&') {
	$rv = $baseurl? $baseurl : getStringFromServer('PHP_SELF');
	$pfx = '?';
	foreach ($args as $k => $v) {
		if ($v === false) {
			continue;
		}
		$rv .= $pfx.urlencode($k).'='.urlencode($v);
		$pfx = $sep;
	}
	return ($ashtml? util_html_encode($rv) : $rv);
}

/**
 * util_sanitise_multiline_submission() – Convert text to ASCII CR-LF
 *
 * @param	string	$text
 *			input string to sanitise
 * @return	string
 *		sanitised string: CR, LF or CR-LF converted to CR-LF
 */
function util_sanitise_multiline_submission($text) {
	/* convert all CR-LF into LF */
	$text = preg_replace("/\015+\012+/m", "\012", $text);
	/* convert all CR or LF into CR-LF */
	$text = preg_replace("/[\012\015]/m", "\015\012", $text);

	return $text;
}

function util_is_html($string) {
	return (strip_tags(util_unconvert_htmlspecialchars($string)) != $string);
}

function util_init_messages() {
	global $feedback, $warning_msg, $error_msg;

	if (PHP_SAPI == 'cli') {
		$feedback = $warning_msg = $error_msg = '';
	} else {
		$feedback = getStringFromCookie('feedback', '');
		if ($feedback) setcookie('feedback', '', time()-3600, '/');

		$warning_msg = getStringFromCookie('warning_msg', '');
		if ($warning_msg) setcookie('warning_msg', '', time()-3600, '/');

		$error_msg = getStringFromCookie('error_msg', '');
		if ($error_msg) setcookie('error_msg', '', time()-3600, '/');
	}
}

function util_save_messages() {
	global $feedback, $warning_msg, $error_msg;

	setcookie('feedback', $feedback, time() + 10, '/');
	setcookie('warning_msg', $warning_msg, time() + 10, '/');
	setcookie('error_msg', $error_msg, time() + 10, '/');
}

/**
 * util_create_file_with_contents() — Securely create (or replace) a file with given contents
 *
 * @param	string	$path		Path of the file to be created
 * @param	string	$contents	Contents of the file
 *
 * @return	boolean	FALSE on error
 */
function util_create_file_with_contents($path, $contents) {
	if (file_exists($path) && !unlink($path)) {
		return false;
	}
	$handle = fopen($path, "x+");
	if ($handle == false) {
		return false;
	}
	fwrite($handle, $contents);
	fclose($handle);
	return true;
}

/**
 * Create a directory in the system temp directory with a hard-to-predict name.
 * Does not have the guarantees of the actual BSD libc function or Python tempfile function.
 * @param	string	$suffix	Append to the new directory's name
 * @param	string	$prefix	Prepend to the new directory's name
 * @return	string	The path of the new directory.
 *
 * Mostly taken from https://gist.github.com/1407245 as a "temporary"
 * workaround to https://bugs.php.net/bug.php?id=49211
 */
function util_mkdtemp($suffix = '', $prefix = 'tmp') {
	$tempdir = sys_get_temp_dir();
	for ($i=0; $i<5; $i++) {
		$id = strtr(base64_encode(util_randbytes(6)), '+/', '-_');
		$path = "{$tempdir}/{$prefix}{$id}{$suffix}";
		if (mkdir($path, 0700)) {
			return $path;
		}
	}
	return false;
}

/**
 * Run a function with only the permissions of a given Unix user
 * Function can be an anonymous
 * Optional arguments in an array
 * @param	string		$username	Unix user name
 * @param	function	$function	function to run (possibly anonymous)
 * @param	array		$params		parameters
 * @return	boolean	true on success, false on error
 */
function util_sudo_effective_user($username, $function, $params=array()) {
	$userinfo = posix_getpwnam($username);
	if ($userinfo === false) {
		return false;
	}

	$pid = pcntl_fork();
	if ( $pid == -1 ) {
		// Fork failed
		exit(1);
	} else if ($pid) {
		pcntl_waitpid($pid, $status);
	} else {
		if (posix_setgid($userinfo['gid']) &&
			posix_initgroups($username, $userinfo['gid']) &&
			posix_setuid($userinfo['uid'])) {
			putenv('HOME='.$userinfo['dir']);
			$function($params);
		}
		//exit(1); // too nice, PHP gracefully quits and closes DB connection
		posix_kill(posix_getpid(), 9);
	}
	return true;
}

function getselfhref($p = array(), $return_encoded = true) {
	global $group_id, $atid, $aid, $is_add;
	$p['group_id'] = $group_id;
	$p['atid'] = $atid;
	if (!$is_add) {
		/* grml… */
		$p['aid'] = $aid;
		$p['artifact_id'] = $aid;
	}
	return util_gethref(false, $p, $return_encoded);
}

/**
 * getThemeIdFromName()
 *
 * @param	string  $dirname	the dirname of the theme
 * @return	int	the theme id
 */
function getThemeIdFromName($dirname) {
	$res = db_query_params ('SELECT theme_id FROM themes WHERE dirname=$1',
			array ($dirname));
	return db_result($res,0,'theme_id');
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
