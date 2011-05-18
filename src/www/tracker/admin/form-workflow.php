<?php
/**
 * Update Artifact Type Form
 *
 * Copyright 2010, FusionForge Team
 * http://fusionforge.org
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

require_once('common/tracker/ArtifactWorkflow.class.php');

$has_error = false;
$efarr = $ath->getExtraFields(array(ARTIFACT_EXTRAFIELDTYPE_STATUS));
if (count($efarr) === 0) {
	$has_error = true;
   	$error_msg .= _('To create a workflow, you need first to create a custom field of type \'Status\'.');
} elseif (count($efarr) !== 1) {
	// Internal error.
	$has_error = true;
	$error_msg .= _('Internal error: Illegal number of status fields (WKFL01).');
}

$ath->adminHeader(array ('title'=> _('Configure workflow'),'pagename'=>'tracker_admin_customize_liste','titlevals'=>array($ath->getName())));

/*
	List of possible user built Selection Boxes for an ArtifactType
*/
if (!$has_error) {
    		
  	$keys=array_keys($efarr);
   	$field_id = $keys[0];
   	$field_name = $efarr[$field_id]['field_name'];

   	$atw = new ArtifactWorkflow($ath, $field_id);

	$elearray = $ath->getExtraFieldElements($field_id);
	$states = $elearray;

?>
	    	
   	<h2><?php printf(_('Allowed initial values for the %1$s field'), $field_name) ?></h2>
	<form action="<?php echo getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID(); ?>" method="post">
	<input type="hidden" name="field_id" value="<?php echo $field_id ?>" />
	<input type="hidden" name="workflow" value="1" />
   	
<?php 
	$from = _('From').' ';
	$to = _('To').' ';
	$init = _('Initial values').' ';

	$title_arr=array();
	$title_arr[]=_('From Value');
	foreach ($elearray as $status) {
		$title_arr[]=$status['element_name'];		
	}
	echo $GLOBALS['HTML']->listTableTop($title_arr, false, ' ');
	echo "\n";

	// Special treatement for the initial value (in the Submit form).
	echo '<tr id="initval"><th style="text-align:left">'.$init.'</th>'."\n";
	$next = $atw->getNextNodes('100');
	foreach ($states as $s) {
		$name = 'wk[100]['. $s['element_id'].']';
		$value = in_array($s['element_id'], $next)? ' checked="checked"' : '';
		$str = '<input type="checkbox" name="'.$name.'"'.$value.' />';
				$str .= ' '.html_image('spacer.gif', 20, 20);
		echo '<td align="center">'.$str.'</td>'."\n";
	}
	echo '</tr>'."\n";
	echo $GLOBALS['HTML']->listTableBottom();

	$count=count($title_arr);
	$totitle_arr = array();
	for ($i=0; $i<$count; $i++) {
		$totitle_arr[] = $title_arr[$i]? $to.$title_arr[$i] : '';
	}
	echo $GLOBALS['HTML']->listTableTop($totitle_arr, false, ' ');
	
	$i=1;
	foreach ($elearray as $status) {
		echo '<tr id="configuring-'.$i++.'"><th style="text-align:left">'.$from.$status['element_name'].'</th>'."\n";
		$next = $atw->getNextNodes($status['element_id']);
		foreach ($states as $s) {
			if ($status['element_id'] !== $s['element_id']) {
				$name = 'wk['.$status['element_id'].']['. $s['element_id'].']';
				$value = in_array($s['element_id'], $next)? ' checked="checked"' : '';
				$str = '<input type="checkbox" name="'.$name.'"'.$value.' />';
				if ($value) {
					$url = getStringFromServer('PHP_SELF').'?group_id='.$group_id.'&amp;atid='.$ath->getID().'&amp;workflow_roles=1&amp;from='.$status['element_id'].'&amp;next='.$s['element_id'];
					$str .= ' <a href="'.$url.'" title="Edit roles">'.html_image('ic/acl_roles20.png', 20, 20, array('alt'=>'Edit Roles')).'</a>';
				} else {
							$str .= ' '.html_image('spacer.gif', 20, 20);
				}
			} else {
				$str = '<input type="checkbox" checked="checked" disabled="disabled" />';
						$str .= ' '.html_image('spacer.gif', 20, 20);
			}
			echo '<td align="center">'.$str.'</td>'."\n";
		}
		echo '</tr>'."\n";
	}
	echo $GLOBALS['HTML']->listTableBottom();

?>
<div class="tips">Tip: Click on <?php echo html_image('ic/acl_roles20.png', 20, 20, array('alt'=> _('Edit Roles'))) ?> to configure allowed roles for a transition (all by default).</div>	
<p>
<input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
</form>
<?php
}

$ath->footer(array());

?>
