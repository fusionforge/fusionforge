<?php

/**
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

global $sys_plugins_path;

$found = false;
if (is_dir($sys_plugins_path.'/svncommitemail/common/')) {
	if (is_file($sys_plugins_path.'/svncommitemail/common/SVNCommitEmailPlugin.class.php')) {
		require_once ($sys_plugins_path.'/svncommitemail/common/SVNCommitEmailPlugin.class.php') ;
		$found = true;
	}
} else {
	if (is_file($sys_plugins_path.'/svncommitemail/include/SVNCommitEmailPlugin.class.php')) {
		require_once ($sys_plugins_path.'/svncommitemail/include/SVNCommitEmailPlugin.class.php') ;
		$found = true;
	}
}

if ($found) {
	$SVNCommitEmailPlugin = new SVNCommitEmailPlugin() ;
	register_plugin ($SVNCommitEmailPlugin) ;	
} else {
	echo 'Plugin svncommitemail not found';
}


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
