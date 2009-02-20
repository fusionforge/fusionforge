<?php
/**
 * GForge Search Engine
 *
 * Copyright 2005 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 */

require_once $gfwww.'search/include/engines/GroupSearchEngine.class.php';

class TrackersGroupSearchEngine extends GroupSearchEngine {
	
	function TrackersGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_TRACKERS, 'TrackersHtmlSearchRenderer', _('This project\'s trackers'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters)) {
			if($this->Group->usesTracker()) {
				return true;
			}
		}
		return false;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
