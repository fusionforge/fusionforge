<?php
/**
  *
  * Project Registration: Starting page
  *
  * This is intro page for project registration, it does not perform any
  * actions.
  *
  * Next in sequence: requirements.php
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');

session_require(array(isloggedin=>1));

$HTML->header(array(title=>"Project Registration",'pagename'=>'register'));
?>

<p>
SourceForge would like to extend an invitation to any 
<A href="http://www.opensource.org">Open Source</A> project to be hosted for no price and
no catch. This is our token of appreciation to the people who help make 
<A href="http://www.opensource.org">Open Source</A> a reality.
</p>

<p><B>The Process</B>

<p>
Registering a project with SourceForge is an easy process, but we do require
a reasonable amount of information in order to automate things 
as much as possible and present your project adequately. Registering 
project consists of the three steps:
<ol>
<li>Submitting a request
<li>Approval of the request
<li>Setting up your project's account
</ol>
</p>

<p>
Currently, you are going to proceed with the first step of the 
process. It should take about 10 minutes. After that, allow 
several days for our review of the request. If it will comply 
with our requirements for hosting (see the next step), your 
project will be approved, and you will receive email 
with directions for future steps.
</p>

<p>
During signup, we will present you with some legal documents 
regarding your account with us. Please do not
ignore these; they are very important to you and your project.
</p>

<p>&nbsp;
<BR><H3 align=center><a href="requirements.php">Step 1: Services and Requirements</a></H3>
</p>

<?php

$HTML->footer(array());

?>

