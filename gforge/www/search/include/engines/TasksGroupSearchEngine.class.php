<?php
/**
 * GForge Search Engine
 *
 * Copyright 2005 (c) Guillaume Smet
 *
 * http://gforge.org
 *
 * @version $Id: GroupSearchEngine.class.php,v 1.2 2004/12/12 23:34:46 gsmet Exp $
 */

require_once('www/search/include/engines/GroupSearchEngine.class.php');

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
