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

echo $HTML->header(array('title'=>$Language->getText('survey_privacy','title')));

?>

<h1><?php echo $Language->getText('survey_privacy','survey_privacy'); ?></h1>
<?php echo $Language->getText('survey_privacy','the_privacy_information'); ?>
</p>

<p><strong><?php echo $Language->getText('survey_privacy','the_team',array($GLOBALS['sys_name'])); ?></strong></p>

<?php

echo $HTML->footer(array());

?>
