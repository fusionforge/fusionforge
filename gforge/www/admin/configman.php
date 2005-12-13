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


require_once('pre.php');
require_once('www/admin/admin_utils.php');

site_admin_header(array('title'=>$Language->getText('admin_index','title')));

function printSelection($checked) {
	global $Language,$feedback;
	
	$config_files = array(); // array that´ll have the config files
	$i = 0;
					
	// check if we can get local.inc
	$handle = fopen('/etc/gforge/local.inc','r+');
	if ($handle) {
		$config_files['local.inc'] = '/etc/gforge/local.inc';
		fclose($handle);
		$i++;
	} else {
		// say we couldn't open local.inc
		$feedback .= $Language->getText('configman','notopenlocalinc');
	}

	//get the directories from the plugins dir
	if (chdir('/etc/gforge/plugins/')) {
		$handle = opendir('.');
		$j = 0;
		while ($filename = readdir($handle)) {
			//Don't add special directories '..' or '.' to the list
			if (($filename!='..') && ($filename!='.') && ($filename!="CVS") ) {
				$handle2 = @opendir($filename); // open the etc dir of the plugin 
				if ($handle2){
					while ($filename2 = readdir($handle2)) {
						if (strstr($filename2,'.conf') || strstr($filename2,'.inc') || ($filename2=='config.php')) {
							$config_files['(' . $filename . ') - ' . $filename2] = '/etc/gforge/plugins/' . $filename . '/' . $filename2;
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
		$feedback .= $Language->getText('configman','notopenplugindir');
	}
	
	echo '<br><div align="center">';
	echo html_build_select_box_from_assoc($config_files,'files',$checked,true);
	echo '<input type="submit" name="choose" value="' . $Language->getText('configman','choose') .'"/>';
	echo '</div>';	
}


?>


<form name="theform" action="<?php echo getStringFromServer('PHP_SELF'); ?>" method="POST">
<?php

printSelection(getStringFromRequest('files'));

if (getStringFromRequest('choose')) {
	
	$filepath = getStringFromRequest('files');
	$handle = fopen($filepath,'r+');
	if ($handle){
		fclose($handle); // we had to open it in r+ because we need to check we'll be able to save it later
		$filedata = file_get_contents($filepath);
		echo '<br><center>' . html_build_rich_textarea('filedata',30,150,$filedata,false) . '</center>';
		echo '<input type="hidden" name="filepath" value="' . $filepath . '">';
		echo '<div align="center"><input type="submit" name="doedit" value="' . $Language->getText('configman','doedit') .'"/></div>';
	} else {
		// say we couldn't open the file
		$feedback .= $Language->getText('configman','notopenfile');
	}
} elseif (getStringFromRequest('doedit')) {
	$filedata = getStringFromRequest('filedata');
	$filedata = str_replace('\"','"',$filedata);
	$filedata = str_replace("\'","'",$filedata);
	$filepath = getStringFromRequest('filepath');
	if ($handle = fopen($filepath,'w')) {
		if (fwrite($handle,$filedata)) {
			// say wrote ok
			$feedback .= $Language->getText('configman','updateok');
		} else {
			// say some problem
			$feedback .= $Language->getText('configman','nowrite');
		}
	} else {
		// say couldn´t open
		$feedback .= $Language->getText('configman','notopenfile');
	}
}


?>

</form>

<?php


site_admin_footer(array());

?>