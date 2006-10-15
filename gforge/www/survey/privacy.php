<?php
/**
 * GForge Survey Facility
 *
 * Portions Copyright 1999-2001 (c) VA Linux Systems
 * The rest Copyright 2002-2004 (c) GForge Team
 * http://gforge.org/
 *
 * @version   $Id$
 *
 * This file is part of GForge.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


require_once('../env.inc.php');
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
