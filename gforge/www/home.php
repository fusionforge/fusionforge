<?php
/**
  *
  * Deprecated Page
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id: home.php,v 1.6 2001/05/22 21:39:30 pfalcon Exp $
  *
  */

$expl_pathinfo = explode('/',$PATH_INFO);

Header ('Location: /projects/'.$expl_pathinfo[1].'/');

?>
