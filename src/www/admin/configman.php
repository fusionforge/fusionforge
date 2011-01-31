<?php
/**
 * FusionForge Config File edit page
 *
 * @version 
 * @author 
 * @copyright 
 * Copyright 2005 GForge, LLC
 * http://fusionforge.org/
 *
 * Daniel A. Pérez danielperez.arg@gmail.com
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

site_admin_header(array('title'=>_('Configuration Manager')));

echo "<h2>".sprintf (_('Configuration from the config API (*.ini files)'))."</h2>" ;

$title_arr = array(_('Variable'),_('Configured value'),_('Result (possibly after interpolation)'));
echo $HTML->listTableTop($title_arr);

$c = FusionForgeConfig::get_instance () ;
$counter = 0 ;
$sections = $c->get_sections () ;
natsort($sections) ;
array_unshift ($sections, 'core') ;
$seen_core = false ;
foreach ($sections as $section) {
	if ($section == 'core') {
		if ($seen_core) {
			continue ;
		}
		$seen_core = true ;
	}
	echo '<tr><th colspan="3"><strong>'.sprintf (_('Section %s'), $section)."</strong></th></tr>\n" ;

	$variables = $c->get_variables ($section) ;
	natsort($variables) ;
	foreach ($variables as $var) {
		echo '<tr '. $HTML->boxGetAltRowStyle($counter++) .'><td>'.$var ;
		if ($c->is_bool ($section, $var)) {
			print " (boolean)" ;
		}
		print "</td><td>" ;
		print htmlspecialchars($c->get_raw_value ($section, $var)) ;
		print "</td><td>" ;
		$v = $c->get_value ($section, $var) ;
		if ($c->is_bool ($section, $var)) {
			if ($v) {
				print "true" ;
			} else {
				print "false" ;
			}
		} else {
			print htmlspecialchars($v);
		}
		print "</td></tr>" ;
	}
}

echo $HTML->listTableBottom();

site_admin_footer(array());

function get_absolute_filename($filename) {
	// Check for absolute path
	if (realpath($filename) == $filename) {
		return $filename;
	}

	// Otherwise, treat as relative path
	$paths = explode(':', get_include_path());
	foreach ($paths as $path) {
		if (substr($path, -1) == '/') {
			$fullpath = $path.$filename;
		} else {
			$fullpath = $path.'/'.$filename;
		}
		if (file_exists($fullpath)) {
			return $fullpath;
		}
	}

	return false;
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
