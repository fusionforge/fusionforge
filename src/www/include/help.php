<?php
/**
 * Misc help page functions
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 * http://fusionforge.org
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
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
