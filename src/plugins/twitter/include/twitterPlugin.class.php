<?php

class twitterPlugin extends ForgeAuthPlugin {

	public function __construct() {

		$this->ForgeAuthPlugin() ;

		$this->name = 'twitter';
		$this->text = 'Twitter'; // To show in the tabs, use...
		$this->_addHook("user_personal_links");//to make a link to the user's personal part of the plugin
		$this->_addHook("usermenu");
		$this->_addHook("userisactivecheckbox"); // The "use ..." checkbox in user account
		$this->_addHook("userisactivecheckboxpost"); //
		
	}

	function usermenu() {
		global $G_SESSION,$HTML;
		$text = $this->text; // this is what shows in the tab
		if ($G_SESSION->usesPlugin($this->name)) {
			echo  $HTML->PrintSubMenu (array ($text), array ('/plugins/twitter/index.php') );
		}
	}
	
}
