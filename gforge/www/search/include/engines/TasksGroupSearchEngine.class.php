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

class TasksGroupSearchEngine extends GroupSearchEngine {
	
	function TasksGroupSearchEngine() {
		$this->GroupSearchEngine(SEARCH__TYPE_IS_TASKS, 'TasksHtmlSearchRenderer', _('This project\'s tasks'));
	}
	
	function isAvailable($parameters) {
		if(parent::isAvailable($parameters)) {
			if($this->Group->usesPM()) {
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
