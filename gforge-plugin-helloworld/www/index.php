<?php
/*
 * Hello world plugin
 *
 * Roland Mas <lolando@debian.org>
 */

require_once('pre.php');

if (!$user_id) {
	exit_error('Error','No User Id Provided');
}

$user = user_get_object($user_id);


if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "That user does not exist.");
} else {
	print $HTML->header(array('title'=>'Hello world','pagename'=>'helloworld'));
	$user_name = $user->getRealName();

	if ($user->usesPlugin("helloworld")) {
		print $HTML->box1_top("$user_name says Hello!");
	} else {
		print $HTML->box1_top("$user_name does not say Hello...");
	}
	print '<A HREF="toggle.php?user_id='.$user_id.'">Toggle!</A>' ;
	print "This is the helloworld plugin.  I hope you enjoy it." ;
	print $HTML->box1_bottom();
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
