<?php
/**
 * GForge Config File edit page
 *
 * @version 
 * @author 
 * @copyright 
 * Copyright 2005 GForge, LLC
 * http://gforge.org/
 *
 * Daniel A. Pérez danielperez.arg@gmail.com
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

require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

site_admin_header(array('title'=>_('Configuration Manager')));
echo '<h1>' . _('Configuration Manager') . '</h1>';

$gfcgfile = get_absolute_filename($gfcgfile);

/**
 * printSelection - prints the select box for the user to get the files to edit
 *
 * @param string the checked item (if any)
 * @param string	the path to the plugins conf dir
 */
function printSelection($checked,$pluginpath) {
	global $feedback, $gfcgfile;
	
	$config_files = array(); // array that'll have the config files
	
	if (strlen($pluginpath)>=1){
		if ($pluginpath[strlen($pluginpath)-1]!='/') {
			$pluginpath .= '/';
		}
	}
					
	// check if we can get local.inc
	@$handle = fopen($gfcgfile,'r+');
	if (! $handle) { 
		// Open readonly but tell you can't write
		$handle = fopen($gfcgfile,'r');
		$feedback .= sprintf(_('Could not open %s file for read/write. Check the permissions for apache.'), $gfcgfile).'<br />';
	}
	if ($handle) {
		$config_files['local.inc'] = $gfcgfile;
		fclose($handle);
	} else {
		// say we couldn't open local.inc
		$feedback .= sprintf(_('Could not open %s for read. Check the permissions for apache.'), $gfcgfile).'<br />';
	}

	//get the directories from the plugins dir
	/*if (chdir($pluginpath)) {
		$handle = opendir('.');
		$j = 0;
		while ($filename = readdir($handle)) {
			//Don't add special directories '..' or '.' to the list
			if (($filename!='..') && ($filename!='.') && ($filename!="CVS") ) {
				$handle2 = @opendir($filename); // open the etc dir of the plugin 
				if ($handle2){
					while ($filename2 = readdir($handle2)) {
						if (strstr($filename2,'.conf') || strstr($filename2,'.inc') || ($filename2=='config.php')) {
							$config_files['(' . $filename . ') - ' . $filename2] = $pluginpath . $filename . '/' . $filename2;
						}
					}
					fclose($handle2);
				}
			}
		}
		fclose($handle);
	} else {
		// say we couldn't get into etc plugins dir
		$feedback .= _('Could not open plugins etc dir for reading. Check the permissions for apache<br>');
	}*/
	
	echo '<br /><div align="center">';
	echo html_build_select_box_from_assoc($config_files,'files',$checked,true);
	echo '&nbsp;<input type="submit" name="choose" value="' . _('Choose') .'"/>';
	echo '</div><br />';	
}

/**
 * getVars - gets the contents of the file and returns an associative array of field names / values
 *
 * @param string	the contents of the file
 * @return array	the results
 */
function getVars($filedata) {
	
	$lines = explode("\n",$filedata);
	$results = array();
	foreach ($lines as $line) {
		if ( substr(trim($line),0,1) == "$"  ) {
			$sep_var = explode("=",$line,2);
			$sep_var[0] = trim($sep_var[0]);
			$sep_var[1] = substr(trim($sep_var[1]),0,strlen(trim($sep_var[1]))-1);
			$results[$sep_var[0]] = $sep_var[1];
		}
	}
	return $results;
}

/**
 * updateVars - updates the values of the vars of the file passed as an argument with the values of the array passed
 *
 * @param unknown_type $vars
 * @param unknown_type $filepath
 */
function updateVars($vars,$filepath) {
	global $feedback;
	
	$filedata = file_get_contents($filepath);
	$lines = explode("\n",$filedata);
	$keys = array_keys($vars);
	for($i=0;$i<(count($vars));$i++) {
		$vars[$keys[$i]] = str_replace('\"','"',$vars[$keys[$i]]);
		$vars[$keys[$i]] = str_replace("\'","'",$vars[$keys[$i]]);
		$currline = $keys[$i] . "=" . $vars[$keys[$i]] . ";";
		//$filedata = preg_replace('/(.*)(' . $keys[$i] . ')([^;]*);(.*)/','/\1\2='.$vars[$keys[$i]].';\n\4/',$filedata);	
		for ($j=0;$j<count($lines);$j++) {
			$mykey = explode("=",$lines[$j]);
			$mykey = trim($mykey[0]);
			if ($mykey == $keys[$i]) {
				$lines[$j] = $currline;
			}
		}
	}
	$filedata = implode("\n",$lines);
	if (@$handle = fopen($filepath,'w')) {
		if (fwrite($handle,$filedata)) {
			// say wrote ok
			$feedback .= sprintf(_('File %s wrote successfully.'), $filepath).'<br />';
		} else {
			// say some problem
			$feedback .= sprintf(_('File %s wasn\'t written or is empty.'), $filepath).'<br />';
		}
	} else {
		// say couldn't open
		$feedback .= sprintf(_("Could not open %s for write. Check the permissions for apache."), $filepath).'<br />';
	}
}

?>


<form name="theform" action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="post">

<!--<?php //echo _('Path were you store the plugin configuration folders. E.g. : /etc/gforge/plugins/'); ?>&nbsp;&nbsp;
<input type="text" size="55" width="55" name="pluginpath" value="<?php //echo getStringFromRequest('pluginpath')?>"/>
<input type="submit" name="changepath" value="<?php //echo _('Change'); ?>"/>
<br />'-->
<?php

//if (getStringFromRequest('pluginpath')) {

//	printSelection(getStringFromRequest('files'),getStringFromRequest('pluginpath'));
	
	if (!getStringFromRequest('doedit')) {
		
		$filepath = $gfcgfile;
		$has_write = true;
		@$handle = fopen($filepath,'r+');
		if (! $handle) {
			// Open readonly but tell you can't write
			$handle = fopen($filepath,'r');
			$has_write = false;
			$feedback .= sprintf(_("Could not open %s file for read/write. Check the permissions for apache."), $filepath).'<br />';
		}
		if ($handle){
			fclose($handle); // we had to open it in r+ because we need to check we'll be able to save it later
			$filedata = file_get_contents($filepath);
			$vars = getVars($filedata); // get the vars from local.inc
			$keys = array_keys($vars);
			sort($keys);
			echo '<h2>Configuration file: '. $filepath.'</h2>';
			$title_arr = array(_('Attribute'),_('On'),_('Off'));
			echo $HTML->listTableTop($title_arr);
			$j = 0;
			for($i=0;$i<(count($keys));$i++) {
				if ( ($vars[$keys[$i]]=="true") || ($vars[$keys[$i]]=="false") ) {
					echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
					'<td>'. $keys[$i] .'</td>';
					$checkedtrue = "";
					$checkedfalse = "";
					($vars[$keys[$i]]=="true")?$checkedtrue=' checked="checked" ':$checkedfalse=' checked="checked" ';
				 	echo '<td style="text-align:center"><input type="radio" name="attributes[' . $keys[$i] . ']" value="true" ' . $checkedtrue . '/>' .'</td>'.
				 	'<td style="text-align:center"><input type="radio" name="attributes[' . $keys[$i] . ']" value="false" ' . $checkedfalse . '/></td>';
					echo '</tr>'."\n";
				}
			}
			echo $HTML->listTableBottom();
			$title_arr = array(_('Attribute'),_('Value'));
			echo $HTML->listTableTop($title_arr);
			for($i=0;$i<(count($keys));$i++) {
				// Strings
				if ( (substr($vars[$keys[$i]],0,1)=='"') ||(substr($vars[$keys[$i]],0,1)=="'") ) {
					echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
				 	'<td>'. $keys[$i] .'</td>';
					echo '<td><input type="text" size=80 name="attributes[' . $keys[$i] . ']" value="'. str_replace('"',"'",$vars[$keys[$i]]) .'"></td>';
					echo '</tr>'."\n";
				}
				// Numbers
				else if ( (ord(substr($vars[$keys[$i]],0,1)) >= 48) && (ord(substr($vars[$keys[$i]],0,1)) <= 57) ) {
					echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
				 	'<td>'. $keys[$i] .'</td>';
					echo '<td><input type="text" size=80 name="attributes[' . $keys[$i] . ']" value="'. trim($vars[$keys[$i]],"'") .'"></td>';
					echo '</tr>'."\n";
				}
				// Others => Not supported
				else {
					echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
				 	'<td>'. $keys[$i] .'</td>';
					echo '<td>Not supported</td>';
					echo '</tr>'."\n";
					$array_rm[$i]=1;
				}
			 	$j++;
			}
			// Remove "Not supported" keys from the keys' array
			$j=0;
			for($i=0;$i<(count($array_rm));$i++) {
				if ( $array_rm[$i] == 1 ) {
					array_splice($array_rm, $i-$j);
					$j++;
				}
			}
			echo $HTML->listTableBottom();
			echo '<br />';
			if ($has_write) {
				echo '<div align="center"><input type="submit" name="doedit" value="' . _('Save') .'"/></div>';
			}
		} else {
			// say we couldn't open the file
			$feedback .= sprintf(_("Could not open %s for read. Check the permissions for apache."), $filepath).'<br />';
		}
	} elseif (getStringFromRequest('doedit')) {
		updateVars(getArrayFromRequest('attributes'),$gfcgfile); // perhaps later we'll update something else, for now it's local.inc
		/*$filedata = getStringFromRequest('filedata');
		$filedata = str_replace('\"','"',$filedata);
		$filedata = str_replace("\'","'",$filedata);
		$filepath = getStringFromRequest('filepath');
		if ($handle = fopen($filepath,'w')) {
			if (fwrite($handle,$filedata)) {
				// say wrote ok
				$feedback .= _('File wrote successfully.<br>');
			} else {
				// say some problem
				$feedback .= _('File wasn\'t written or is empty.<br>');
			}
		} else {
			// say couldn't open
			$feedback .= _('Could not open the file for read/write. Check the permissions for apache<br>');
		}*/
	}
//}


?>

</form>
<hr/>

<?php
echo "<h2>".sprintf (_('Configuration from the config API (*.ini files) (experimental)'))."</h2>" ;

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
	echo '<tr><th colspan="3"><strong>'.sprintf (_('Section %s'), $section)."</th></tr>" ;

	$variables = $c->get_variables ($section) ;
	natsort($variables) ;
	foreach ($variables as $var) {
		echo '<tr '. $HTML->boxGetAltRowStyle($counter++) .'><td>'.$var ;
		if ($c->is_bool ($section, $var)) {
			print " (boolean)" ;
		}
		print "</td><td>" ;
		print $c->get_raw_value ($section, $var) ;
		print "</td><td>" ;
		$v = $c->get_value ($section, $var) ;
		if ($c->is_bool ($section, $var)) {
			if ($v) {
				print "true" ;
			} else {
				print "false" ;
			}
		} else {
			print "$v" ;
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
