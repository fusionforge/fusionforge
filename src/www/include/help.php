<?php
/**
 * Misc help page functions
 *
 * SourceForge: Breaking Down the Barriers to Open Source Development
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://sourceforge.net
 *
 */

/**
 * help_button() - Show a help button.
 *
 * @param		string	The button type
 * @param		int		The trove category ID
 */
function help_button($type,$helpid) {
	if ($type == 'trove_cat') {
		return ('<a href="javascript:help_window(\'/help/trove_cat.php'
			.'?trove_cat_id='.$helpid.'\')"><strong>(?)</strong></a>');
	}
}

/**
 * help_header() - Show a help page header
 *
 * @param		string	Header title
 */
function help_header($title) {
?>
<!DOCTYPE html
	PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo _('en') ?>" lang="<?php echo _('en') ?>">
<head>
<title><?php print $title; ?></title>
</head>
<body>
<h1><?php echo forge_get_config ('forge_name'); ?> Site Help System:</h1>
<h2><?php print $title; ?></h2>
<hr />
<?php
}


/**
 * help_footer() - Show a help page footer
 */
function help_footer() {
?>
</body>
</html>
<?php
}

?>
