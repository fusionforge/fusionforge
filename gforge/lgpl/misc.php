<?php
/**
 *  misc.php - allows non-free software to be linked safely to GForge
 *
 *  THIS FILE IS RELEASED UNDER THE LGPL
 *
 *  ANY MODIFICATIONS MUST BE ALSO RELEASED UNDER LGPL
 *
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
