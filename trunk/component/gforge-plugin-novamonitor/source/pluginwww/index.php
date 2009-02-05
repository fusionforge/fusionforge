<?php

/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 */
 
require_once('../../env.inc.php');
require_once $gfwww.'include/pre.php';

	global $group_id;
	
site_project_header (array ('title'=>dgettext ("gforge-plugin-novamonitor", "Monitor"), 'group'=>$group_id, 'toptab'=>'novamonitor'));

if( !session_loggedin() ){
    exit_permission_denied();
}    


if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object ($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

if ($g->usesPlugin ('novamonitor') == false) {
	exit_error (dgettext ('general', 'error'), dgettext ('gforge-plugin-novamonitor', 'Le plugin novamonitor n\'est pas activ&eacute; pour ce projet.'));
}

$url = "http://localhost:8080/monitoring";

echo "\n\n\n\n";
echo "<script type='text/javascript'> "	;
echo "\n";
echo "var hauteur = 0;"; 
echo "if( typeof( window.innerWidth ) == 'number' ) {";
echo "  hauteur = window.innerHeight;";
echo "  }";
echo "else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {";
echo "  hauteur = document.documentElement.clientHeight;";
echo "  }";
echo "else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {";
echo "  hauteur = document.body.clientHeight;";
echo "  }";
echo "else {";
echo "  hauteur = -1;";
echo "  }";
echo "hauteur = hauteur - 150;";
echo "\n";
echo "document.write(\"<iframe name='novamonitor' SRC='";
echo $url;
echo "' scrolling='auto' height='\"+hauteur+\"' width='100%' FRAMEBORDER='yes'></iframe>\");";
echo "\n";
echo "</script> ";
echo "\n";
echo "<br/><div align='right'><a href='";
echo $url;
echo "' target='_blank'>";
echo dgettext ('gforge-plugin-novamonitor', 'Ouvrir le monitoring &agrave; l\'ext&eacute;rieur');
echo "</a></div>";
echo "\n\n\n\n";
site_project_footer (array ());
 ?>
