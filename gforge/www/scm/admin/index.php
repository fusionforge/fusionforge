<?php
/**
 * GForge SCM Frontend
 *
 * Copyright 2004 (c) Roland Mas, GForge LLC
 *
 * @version   $Id$
 * @author Tim Perdue tim@gforge.org
 * @date 2004-05-19
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

require_once('pre.php');
require_once('www/scm/include/scm_utils.php');

// Check permissions
session_require(array('group'=>$group_id,'admin_flags'=>'A'));

scm_header(array('title'=>$Language->getText('scm_index','scm_repository'),'group'=>$group_id));



if ($submit) {
	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	$scmvars = array_keys ($_GET) ;
	foreach ($_GET as $key => $value) {
		foreach ($scm_list as $scm) {
			if ($key == strstr($key, $scm . "_")) {
				$hook_params[$key] = $value ;
			}
		}
	}
	$hook_params['scmradio'] = $scmradio ;
	plugin_hook ("scm_admin_update", $hook_params) ;
}

?>
<form action="<?php echo $PHP_SELF; ?>">
<?php

	$hook_params = array () ;
	$hook_params['group_id'] = $group_id ;
	plugin_hook ("scm_admin_page", $hook_params) ;
?>
<input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
<input type="submit" name="submit" value="Update">
</form>
<?php

scm_footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
