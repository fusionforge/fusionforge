<?php

class oauthconsumerPlugin extends ForgeAuthPlugin {

	public function __construct() {

		$this->ForgeAuthPlugin() ;

		$this->name = 'oauthconsumer';
		$this->text = 'OAuth Consumer'; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user's personal part of the plugin
		$this->_addHook("usermenu");
		$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost"); //
		
	}

	function usermenu() {
		global $G_SESSION,$HTML;
		$text = $this->text; // this is what shows in the tab
		if ($G_SESSION->usesPlugin($this->name)) {
			echo  $HTML->PrintSubMenu (array ($text), array ('/plugins/oauthconsumer/index.php') );
		}
	}
	
}
