<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//
// $Id: home.php,v 1.5 2000/08/06 21:41:06 tperdue Exp $

$expl_pathinfo = explode('/',$PATH_INFO);

Header ('Location: /projects/'.$expl_pathinfo[1].'/');

?>
