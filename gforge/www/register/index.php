<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id$

require "pre.php";    // Initial db and session library, opens session
session_require(array(isloggedin=>1));
$HTML->header(array(title=>"Project Registration"));
?>

<H2>SourceForge Project Registration</H2>

<p>
Welcome to Debian Sourceforge.  You are going to create a project,
with which you will then be able to use all the Sourceforge tools.
</p>

<p><B>The Process</B>

<P>
Registering a project with Sourceforge is an easy process, but we
do require a lot of information in order to automate things as much as
possible. The entire process should take about 10 minutes.

<P>
During signup, we will present you with some legal documents regarding
your account with us. Please do not ignore these; they are very
important to you and your project.

<p>&nbsp;
<BR><H3 align=center><a href="requirements.php">Step 1: Services and Requirements</a></H3>
</p>

<?php
$HTML->footer(array());

?>

