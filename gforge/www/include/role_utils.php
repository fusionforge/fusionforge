<?php
/*
 * Copyright 2004 GForge, LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 *
 * This file is part of GForge.
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
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

function role_box ($group_id,$name,$selected='xzxzxz') {
	global $roleres;
	if (!$roleres) {
		$roleres=db_query("SELECT role_id,role_name 
			FROM role WHERE group_id='$group_id'");
	}
	return html_build_select_box($roleres,$name,$selected,false);
}

?>
