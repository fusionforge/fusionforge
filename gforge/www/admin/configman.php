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
 * Daniel A. P�rez danielperez.arg@gmail.com
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
require_once('pre.php');
require_once('www/admin/admin_utils.php');

site_admin_header(array('title'=>_('Site admin')));

/**
 * printSelection - prints the select box for the user to get the files to edit
 *
 * @param string the checked item (if any)
 * @param string	the path to the plugins conf dir
 */
function printSelection($checked,$pluginpath) {
	global $Language,$feedback,$sys_etc_path;
	
	$config_files = array(); // array that'll have the config files
	$i = 0;
	
	if (strlen($pluginpath)>=1){
		if ($pluginpath[strlen($pluginpath)-1]!='/') {
			$pluginpath .= '/';
		}
	}
					
	// check if we can get local.inc
	@$handle = fopen($sys_etc_path . '/local.inc','r+');
	if (! $handle) { 
		// Open readonly but tell you can't write
		$handle = fopen($sys_etc_path . '/local.inc','r');
		$feedback .= _('Could not open local.inc file for read/write. Check the permissions for apache<br>');
	}
	if ($handle) {
		$config_files['local.inc'] = $sys_etc_path . '/local.inc';
		fclose($handle);
		$i++;
	} else {
		// say we couldn't open local.inc
		$feedback .= _('Could not open local.inc file for read/write. Check the permissions for apache<br>');
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
							$i++;						
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
	
	echo '<br><div align="center">';
	echo html_build_select_box_from_assoc($config_files,'files',$checked,true);
	echo '&nbsp;<input type="submit" name="choose" value="' . _('Choose') .'"/>';
	echo '</div><br>';	
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
		if ( (strstr($line,"true") || strstr($line,"false")) && (!strstr($line,"//"))) { // get the true / false vars
			$sep_var = explode("=",$line);
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
	global $Language,$feedback;
	
	$filedata = file_get_contents($filepath);
	$lines = explode("\n",$filedata);
	$keys = array_keys($vars);
	for($i=0;$i<(count($vars));$i++) {
		$currline = $keys[$i] . "=" . $vars[$keys[$i]] . ";";
		//$filedata = preg_replace('/(.*)(' . $keys[$i] . ')([^;]*);(.*)/','/\1\2='.$vars[$keys[$i]].';\n\4/',$filedata);	
		for ($j=0;$j<count($lines);$j++) {
			if (strstr($lines[$j],$keys[$i])) {
				$lines[$j] = $currline;
			}
		}
	}
	$filedata = implode("\n",$lines);
	if (@$handle = fopen($filepath,'w')) {
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
	}
}

?>


<form name="theform" action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="POST">

<!--<?php //echo _('Path were you store the plugin configuration folders. E.g. : /etc/gforge/plugins/'); ?>&nbsp;&nbsp;
<input type="text" size="55" width="55" name="pluginpath" value="<?php //echo getStringFromRequest('pluginpath')?>"/>
<input type="submit" name="changepath" value="<?php //echo _('Change'); ?>"/>
<br>'-->
<?php

//if (getStringFromRequest('pluginpath')) {

	printSelection(getStringFromRequest('files'),getStringFromRequest('pluginpath'));
	
	if (getStringFromRequest('choose')) {
		
		$filepath = getStringFromRequest('files');
		@$handle = fopen($filepath,'r+');
		if (! $handle) {
			// Open readonly but tell you can't write
			$handle = fopen($sys_etc_path.'/local.inc','r');
			$feedback .= _('Could not open the file for read/write. Check the permissions for apache<br>');
		}
		if ($handle){
			fclose($handle); // we had to open it in r+ because we need to check we'll be able to save it later
			$filedata = file_get_contents($filepath);
			$vars = getVars($filedata); // get the vars from local.inc
			$keys = array_keys($vars);
			sort($keys);
			$title_arr = array(_('Attribute'),_('On'),_('Off'));
			echo $HTML->listTableTop($title_arr);
			$j = 0;
			for($i=0;$i<(count($keys));$i++) {
				$checkedtrue = "";
				$checkedfalse = "";
				($vars[$keys[$i]]=="true")?$checkedtrue=' CHECKED ':$checkedfalse=' CHECKED ';
				echo '<tr '. $HTML->boxGetAltRowStyle($j+1) .'>'.
			 	'<td>'. $keys[$i] .'</td>'.
			 	'<td style="text-align:center"><input type="radio" name="attributes[' . $keys[$i] . ']" value="true" ' . $checkedtrue . '>' .'</td>'.
			 	'<td style="text-align:center"><input type="radio" name="attributes[' . $keys[$i] . ']" value="false" ' . $checkedfalse . '></td></tr>';
			 	$j++;
			}
			echo $HTML->listTableBottom();
			/*echo '<br><center>' . html_build_rich_textarea('filedata',30,150,$filedata,false) . '</center>';
			echo '<input type="hidden" name="filepath" value="' . $filepath . '">';*/
			echo '<br><div align="center"><input type="submit" name="doedit" value="' . _('Save') .'"/></div>';
		} else {
			// say we couldn't open the file
			$feedback .= _('Could not open the file for read/write. Check the permissions for apache<br>');
		}
	} elseif (getStringFromRequest('doedit')) {
		updateVars(getArrayFromRequest('attributes'),$sys_etc_path . '/local.inc'); // perhaps later we'll update something else, for now it's local.inc
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

<?php


site_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
