<?php
/**
 * update-fields.php - Code to update artifacts with fields configured by admin
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
$resultc=$ath->getArtifactChoices($ah->getID());
$rows=db_numrows($result);
$setrows=db_numrows($resultc);
$changect=0;
$transferct=0;
if($result &&$rows > 0) {
	for ($i=0; $i < $rows; $i++) {
	if ($i < $setrows) {
		if (db_result($resultc,$i,'choice_id') != $extra_fields_choice[$i]){
			$ah->updateExtraFields(db_result($resultc,$i,'id'),$extra_fields_choice[$i]);
			$old=(db_result($resultc,$i,'choice_id'));
			$oldnames=$ath->getBoxOptionsName($old);
			$ah->addHistory(db_result($result,$i,'selection_box_name'),db_result($oldnames,'0','box_options_name'));				
			$changect=$changect+1;
		}	
		}else{
			$ah->createExtraFields($extra_fields_choice[$i]);
		if ($extra_fields_choice[$i] !== '100') {
			$transferct=$transferct+1;
		}
		}
	}
	unset ($extra_fields_choice);
}	
?>

