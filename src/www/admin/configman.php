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

require_once '../env.inc.php';
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

$config_depends_on = array(
	'ftp_upload_dir' => 'use_ftp',
	'https_port' => 'use_ssl',
	'images_secure_url' => 'use_ssl',
	'jabber_host' => 'use_jabber',
	'jabber_port' => 'use_jabber',
	'jabber_password' => 'use_jabber',
	'jabber_user' => 'use_jabber',
	'project_auto_approval_user' => 'project_auto_approval',
	'shell_host' => 'use_shell',
);

class configCheck {
	static function account_manager_type($v) { return ($v === 'pgsql'); }
	static function chroot($v) { return is_dir($v); }
	static function config_path($v) { return is_dir($v); }
	static function data_path($v) { return is_dir($v); }
	static function default_theme($v) { return db_numrows(db_query_params('SELECT * FROM themes WHERE dirname=$1', array($v))) === 1; }
	static function extra_config_dirs($v) { return is_dir($v); }
	static function ftp_upload_dir($v) { return is_dir($v); }
	static function gitweb_cmd($v) { return is_file($v); }
	static function groupdir_prefix($v) { return is_dir($v); }
	static function homedir_prefix($v) { return is_dir($v); }
	static function installation_environment($v) { return in_array($v, array('production', 'integration', 'development')); }
	static function jpgraph_path($v) { return is_dir($v); }
	static function log_path($v) { return is_dir($v); }
	static function mailman_path($v) { return is_dir($v); }
	static function plugins_path($v) { return is_dir($v); }
	static function project_auto_approval_user($v) { return db_numrows(db_query_params('SELECT * FROM users WHERE user_name=$1', array($v))) === 1; }
	static function repos_path($v) { return is_dir($v); }
	static function scm_snapshots_path($v) { return is_dir($v); }
	static function scm_tarballs_path($v) { return is_dir($v); }
	static function sendmail_path($v) { return is_file($v); }
	static function session_key($v) { return ($v !== 'foobar'); }
	static function source_path($v) { return is_dir($v); }
	static function news_group($v) { return db_numrows(db_query_params('SELECT * FROM groups WHERE group_id=$1', array($v))) === 1; }
	static function stats_group($v) { return db_numrows(db_query_params('SELECT * FROM groups WHERE group_id=$1', array($v))) === 1; }
	static function template_group($v) { return db_numrows(db_query_params('SELECT * FROM groups WHERE group_id=$1', array($v))) === 1; }
	static function peer_rating_group($v) { return db_numrows(db_query_params('SELECT * FROM groups WHERE group_id=$1', array($v))) === 1; }
	static function themes_root($v) { return is_dir($v); }
	static function upload_dir($v) { return is_dir($v); }
	static function url_root($v) { return is_dir($v); }
}

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
		if (isset($config_depends_on[$var]) &&
			(!$c->get_value($section, $config_depends_on[$var]))) {
			continue;
		}
		$v = $c->get_value ($section, $var) ;

		if (method_exists('configCheck', $var)) {
			$class = configCheck::$var($v)? 'class="good_value"': 'class="wrong_value"';
		} else {
			$class = '';
		}
		echo '<tr '. $HTML->boxGetAltRowStyle($counter++) .'><td>'.$var ;
		if ($c->is_bool ($section, $var)) {
			print " (boolean)" ;
		}
		print "</td><td>" ;
		print htmlspecialchars($c->get_raw_value ($section, $var)) ;
		print "</td><td $class>" ;
		if ($c->is_bool ($section, $var)) {
			if ($v) {
				print "true" ;
			} else {
				print "false" ;
			}
		} else {
			print htmlspecialchars($v);
		}
		print "</td></tr>\n" ;
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
