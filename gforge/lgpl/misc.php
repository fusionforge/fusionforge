<?php
/**
 * misc.php - allows non-free software to be linked safely to GForge
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */

function html_header($title) {

	?>
	<html>
	<head>
	<title><?php echo $title; ?></title>
	</head>
	<body bgcolor="#FFFFF">
	<?php

}

function html_footer() {
	global $feedback;

	if ($feedback) {
		echo '<br /><font color="red"><strong>'.$feedback.'</strong></font>';;
	}

	?>
	</body>
	</html>
	<?php
}

function abort_page ($title,$message) {

	html_header($title);
	echo "<h2>$message</h2>";
	html_footer();

}

function &result_toarray($result, $col=0) {

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

?>
