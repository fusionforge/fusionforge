#! /usr/bin/php
<?php
/**
 * Copyright 2011 Roland Mas
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require (dirname(__FILE__).'/../www/env.inc.php');
require_once $gfcommon.'include/pre.php';
			 
$res=db_query_params('CREATE FUNCTION upgrade_default_role_to_admin () RETURNS void AS $$
DECLARE
	g groups%ROWTYPE ;
BEGIN
	FOR g IN SELECT * FROM groups
	LOOP
		UPDATE user_group SET role_id=(
		       SELECT min(r.role_id)
		       FROM role r JOIN role_setting rs USING (role_id)
		       WHERE r.group_id=g.group_id 
		       	     AND rs.section_name=\'projectadmin\'
			     AND rs.value=\'A\'
		       )
		WHERE role_id=1
		      AND group_id=g.group_id;
	END LOOP ;

END ;
$$ LANGUAGE plpgsql', array());

$res=db_query_params('SELECT upgrade_default_role_to_admin()', array());

$res=db_query_params('DROP FUNCTION upgrade_default_role_to_admin()', array());
?>
