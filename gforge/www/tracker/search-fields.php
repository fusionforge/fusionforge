<?php
/**
 * search-fields.php - code to handle browsing with fields configured by admin
 *
 * Copyright 2004 (c) Anthony J. Pugliese
 *
 * @version   $Id$
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
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 */
$result=$ath->getSelectionBoxes();
$rows=db_numrows($result);
if($result &&$rows > 0)  {
	foreach($_POST as $KEY=>$post_value){
	$value_array[]=$post_value;
	}
	if (!empty($post_value)) {
		$post_value=implode(",",$value_array);
		$post_value=explode(",",$post_value);
		$source=$post_value[0];
		if ($source == 'custom'){
		for ($i=0; $i < $rows; $i++) {
			$value[$i]=$post_value[$i+6];
		}
		}
	
	}
}
?>

