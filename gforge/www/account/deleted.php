<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: deleted.php,v 1.8 2000/09/12 22:09:41 tperdue Exp $

require "pre.php";    
site_user_header(array(title=>"Deleted Account"));
?>

<P><B>Deleted Account</B>

<P>Your account has been deleted. If you have questions regarding your deletion,
please email <A HREF="mailto:staff@<?php echo $GLOBALS['sys_default_domain']; ?>">staff@<?php echo $GLOBALS['sys_default_domain']; ?></A>.
Inquiries through other channels will be directed to this address.

<?php
site_user_footer(array());

?>
