<?php
/**
 * Misc help page functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 * @version   $Id$
 */

/**
 * help_button() - Show a help button.
 *
 * @param		string	The button type
 * @param		int		The trove category ID
 */
function help_button($type,$helpid) {
	if ($type == 'trove_cat') {
		return ('<A href="javascript:help_window(\'/help/trove_cat.php'
			.'?trove_cat_id='.$helpid.'\')"><B>(?)</B></A>');
	}
}

/**
 * help_header() - Show a help page header
 *
 * @param		string	Header title
 */
function help_header($title) {
?>
<HTML>
<HEAD>
<TITLE><?php print $title; ?></TITLE>
</HEAD>
<BODY bgcolor="#FFFFFF">
<H4>SourceForge Site Help System:</H4>
<H2><?php print $title; ?></H2>
<HR>
<?php
}


/**
 * help_footer() - Show a help page footer
 */
function help_footer() {
?>
</BODY>
</HTML>
<?php
}

?>
