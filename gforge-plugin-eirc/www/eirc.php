<?php
/*
 * EIRC plugin
 *
 * Christian Bayle <bayle@debian.org>
 */

require_once('pre.php');

if (!$user_id) {
	exit_error('Error','No User Id Provided');
}

$user = user_get_object($user_id);


if (!$user || !is_object($user) || $user->isError() || !$user->isActive()) {
	exit_error("Invalid User", "That user does not exist.");
} else {
	$user_name = $user->getRealName();
	$unix_name = $user->getUnixName();
	$result = db_query("SELECT groups.unix_group_name "
	                . "FROM groups,user_group "
	                . "WHERE groups.group_id=user_group.group_id "
	                . "AND user_group.user_id=$user_id"
	                . "AND groups.status='A'");
        $rows=db_numrows($result);
	$channels="";
	if (!$result || $rows < 1) {
		$channels="#eirc,";
	} else {
		for ($i=0; $i<$rows; $i++) {
			$channels = $channels . '#' . db_result($result, $i, 'unix_group_name') . ',';
		}
	}
	if ($group_id){
		$group = group_get_object($group_id);
		$channels = $channels . '#' . $group->getUnixName() . ',';
	}
	print $HTML->boxTop("Eteria IRC Client for $user_name");
	
?>
  <applet code="EIRC" archive="EIRC.jar,EIRC-gfx.jar" width="620" height="400">
   <!-- Uncomment the line below to use signed CABs /-->
   <!--param name="cabinets" value="EIRC.cab,EIRC-gfx.cab" /-->
   <!--param name="server" value="localhost" /-->
   <!--param name="port" value="6667" /-->
   <!--param name="mainbg" value="#424242" /-->
   <param name="mainbg" value="#C0C0C0" />
   <param name="mainfg" value="#000000" />
   <param name="textbg" value="#FFFFFF" />
   <param name="textfg" value="#000000" />
   <param name="selbg" value="#00007F" />
   <param name="selfg" value="#FFFFFF" />
   <param name="channel" value="<?php echo $channels; ?>" />
   <param name="titleExtra" value=" - EIRC" />
   <param name="username" value="<?php echo $unix_name; ?>" />
   <param name="realname" value="<?php echo $user_name; ?>" />
   <param name="nickname" value="<?php echo $unix_name; ?>" />
   <!--param name="password" value="" /-->
   <!--param name="servPassword" value="" /-->
   <!--param name="servEmail" value="" /-->
   <param name="login" value="1" />
   <!--param name="spawn_frame" value="1" /-->
   <!--param name="frame_width" value="620" /-->
   <!--param name="frame_height" value="400" /-->
   <!--param name="language" value="en" /-->
   <!--param name="country" value="US" /-->

   <h1>Eteria IRC Client</h1>
   <p>
    Sorry, but you need a Java 1.1.x enabled browser to use EIRC.</p>
  </applet>
<?php
	print $HTML->boxBottom();
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
