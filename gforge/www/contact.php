<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: contact.php,v 1.14 2000/12/05 20:05:23 dbrogdon Exp $

require "pre.php";    // Initial db and session library, opens session
$HTML->header(array('title'=>'Contact SourceForge'));
?>

<p>This is the contact page for <b>SourceForge.net</b> -- if you are having a problem with a project
hosted by us please send your questions/info to the support/bug manager for that project. You can
access those features by visiting that project's development home page at: 
sourceforge.net/projects/&lt;project name&gt; and clicking the approrpiate tool.</p>

<p>If you have a support request, bug report or wish to communicate directly with the SourceForge 
<A href="staff.php">staff</a>, please take care in using the appropriate means:</p>

<ul>
	<li>To Request support for your project or account, visit the <a href="/support/?func=addsupport&group_id=1">SourceForge Support Manager</a></li>
	<li>If you've found a bug in SourceForge, please use the <a href="/bugs/?func=addbug&group_id=1">SourceForge Bug Tracker</a></li>
	<li>All press inquiries should be directed to <a href="mailto:eureka@valinux.com">Eureka Endo</a>, Press Relations Manager, VA Linux Systems.</li>
</ul>

<?php
$HTML->footer(array());
?>
