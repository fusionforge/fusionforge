<?php

class EIRCPlugin extends Plugin {
	function EIRCPlugin () {
		$this->Plugin() ;
		$this->name = "eirc" ;
		$this->hooks[] = "usermenu" ;
		$this->hooks[] = "javascript" ;
		$this->hooks[] = "project_after_description" ;
	}

	function CallHook ($hookname, $params) {
		global $G_SESSION, $HTML, $group_id;
		if ($hookname == "usermenu") {
			$text = "EIRC" ;
			echo ' | ' . $HTML->PrintSubMenu (array ($text),
						  array ('javascript:eirc_window("/plugins/eirc/eirc.php?user_id=' . $G_SESSION->getId() . '")' ));
		} elseif ($hookname == "project_after_description") {
			if ($G_SESSION) {
				echo '
				<P><B><A href=javascript:eirc_window("/plugins/eirc/eirc.php?user_id=' . $G_SESSION->getId() . '&group_id=' . $group_id . '") >Chat online</A></B>
				';
			} else {
				echo '
				<P><B>Si vous &eacute;tiez <A HREF=/account/login.php>connect&eacute;</A> vous pourriez participer au Chat online</B>
				';
			}
		} elseif ($hookname == "project_public_areas") {
			// ...
		} elseif ($hookname == "javascript") {
			echo '
	<!--
        function eirc_window(eircurl) {
	                EIRCWin = window.open( eircurl,"EIRC","scrollbars=no,resizable=no,toolbar=no,height=450,width=650");
	}
	// -->
			' ;

			
		}
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
