<?php
/**
 * Docman Editor
 *
 * Copyright 2004 (c) GForge LLC
 *
 * @version
 * @author Daniel Perez	daniel@gforgegroup.com
 * @date 2003-03-16
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

// This file is a popup window to edit docman
//	By Daniel A. P�rez 2005

require_once('../env.inc.php');
require_once('pre.php');

$group_id = getIntFromRequest('group_id'); //get the group id
echo '<script>';
echo '
function getEditorValue( instanceName ) 
{  
  // Get the editor instance that we want to interact with.
  var oEditor = FCKeditorAPI.GetInstance( instanceName ) ;
  
  // Get the editor contents as XHTML.
  return oEditor.GetXHTML( true ) ;  // "true" means you want it formatted.
}
function setEditorValue( instanceName, text )
{  
  // Get the editor instance that we want to interact with.
  var oEditor = FCKeditorAPI.GetInstance( instanceName ) ;
  
  // Set the editor contents.
  oEditor.SetHTML( text ) ;
}
';
echo '</script>';
echo '<form name="theform">';
$params['name'] = 'data';
$params['width'] = "800";
$params['height'] = "500";
$params['group'] = $group_id;
plugin_hook("text_editor",$params);
$editor = true;
if (!$GLOBALS['editor_was_set_up']) {
	//if we don�t have any plugin for text editor, display a simple textarea edit box
	$editor = false;
	echo '<textarea name="data" rows="15" cols="100" wrap="soft"></textarea><br />';
	echo '<script>	
			window.document.theform.data.value = window.opener.document.adddata.data.value;
		</script>';
} else {
	// we must make it wait a bit or else the fckeditor object won�t be set yet
	echo '<script type="text/javascript">
		setTimeout("setEditorValue(\'data\',window.opener.document.adddata.data.value)",2250);
	</script>
	';
}


unset($GLOBALS['editor_was_set_up']);

if ($editor) {
	echo '<br><div align="right"><input type="submit" value="update" onclick="window.opener.document.adddata.data.value=getEditorValue(\'data\');window.close();"/></div>';
} else {
	echo '<br><div align="right"><input type="submit" value="update" onclick="window.opener.document.adddata.data.value=window.document.theform.data.value;window.close();"/></div>';
}
echo '</form>';

?>
