<?php
/**
  *
  * SourceForge Survey Facility
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


require_once('pre.php');
require_once('vote_function.php');
require_once('www/survey/survey_utils.php');

echo $HTML->header(array('title'=>'Survey'));

?>

<h1>Survey Privacy</h1>

<p>The information collected in these surveys will never be
sold to third parties or used to solicit you to purchase
any goods or services.</p>

<p>This information is being gathered to build a profile
of the projects and developers being surveyed. That profile
will help visitors to the site understand the quality of a
given project.</p>

<p>The ID's of those who answer surveys are suppressed
and not viewable by project administrators or the public
or third parties.</p>

<p>The information gathered is used only in aggregate
form, not to single out specific users or developers.</p>

<p>If any changes are made to this policy, it will affect
only future data that is collected and the user will of
course have the ability to 'opt-out'.</p>

<p><strong>The <?php echo $GLOBALS['sys_name']; ?> Team</strong></p>

<?php

echo $HTML->footer(array());

?>
