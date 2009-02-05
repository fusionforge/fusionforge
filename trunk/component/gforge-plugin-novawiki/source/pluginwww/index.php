<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
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
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
 
if( !session_loggedin() ){
    exit_permission_denied();
}    


if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

session_start();

$_SESSION['nova_project'] = $g->getUnixName();
$_SESSION['nova_project_name'] = $g->getPublicName();

$u =& user_get_object(user_getid());
$lang = $u->getLanguage();
switch ($lang) {
	case 1: 
		$_SESSION['nova_lang'] = "en";
		break;
	case 4: 
		$_SESSION['nova_lang'] = "es";
		break;
	case 7: 
		$_SESSION['nova_lang'] = "fr";
		break;
	default: 
		$_SESSION['nova_lang'] = "en";
		break;
}
if(!isset($title)) {
	$title = "";
}
site_project_header (array ('title'=>$title, 'group'=>$group_id, 'toptab'=>'novawiki'));
?>

<iframe src="wiki/index.php" width="100%" height="400px" name="novawiki" >
</iframe>

<?php
site_project_footer (array ());
?>

