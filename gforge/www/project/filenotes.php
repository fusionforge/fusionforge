<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: filenotes.php,v 1.13 2000/08/12 04:30:41 tperdue Exp $

if ((!$group_id) && $form_grp) $group_id=$form_grp;

header ("Location: showfiles.php?group_id=$group_id");

?>
