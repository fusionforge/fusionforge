<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 * Copyright 2013, Franck Villaume - TrivialDev
 *
 * This file is a part of Fusionforge.
 *
 * Fusionforge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Fusionforge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fusionforge. If not, see <http://www.gnu.org/licenses/>.
 */

class WidgetLayout_Row {
	var $id;
	var $rank;
	var $columns;
	var $layout;

	function WidgetLayout_Row($id, $rank) {
		$this->id      = $id;
		$this->rank    = $rank;
		$this->columns = array();
	}

	function setLayout(&$layout) {
		$this->layout =& $layout;
	}

	function add(&$c) {
		$this->columns[] =& $c;
		$c->setRow($this);
	}

	function display($readonly, $owner_id, $owner_type) {
		echo '<table id="mainwidget_table'.$this->id.'" class="fullwidth "><tbody>' . "\n";
		echo '<tr style="vertical-align:top;">' . "\n";
		$last = count($this->columns) - 1;
		$i = 0;
		foreach($this->columns as $key => $nop) {
			$this->columns[$key]->display($readonly, $owner_id, $owner_type, $is_last = ($i++ == $last));
		}
		echo '</tr>' . "\n";
		echo '</tbody></table>' . "\n";
	}

	function getColumnIds() {
		$ret = array();
		foreach($this->columns as $key => $nop) {
			$ret[] = $this->columns[$key]->getColumnId();
		}
		return $ret;
	}
}
