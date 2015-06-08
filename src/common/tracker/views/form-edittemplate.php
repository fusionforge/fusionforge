<?php
/**
 * Tracker Facility
 *
 * Copyright 2010 (c) FusionForge Team
 * Copyright 2015, Franck Villaume - TrivialDev
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

global $HTML;

$title = sprintf(_('Edit Layout Template for %s'), $ath->getName()) ;
$ath->adminHeader(array('title'=>$title, 'modal'=>1));

$params = array() ;
$params['body'] = isset($body)? $body : '<table>'.$ath->getRenderHTML(array(),'DETAIL').'</table>';
$params['height'] = "500";
$params['group'] = $group_id;
$params['content'] = '<textarea name="body"  rows="30" cols="80">' . $params['body'] . '</textarea>';
plugin_hook_by_reference("text_editor",$params);

?>
<h2>Important</h2>
<ul>
    <li>Keep the one table format with two columns table layout, do not add strings before or after the table.</li>
	<li>All template variables (named like {$...}) should be left untouched.</li>
	<li>Once a template model in use, if you add/remove custom fields, you'll have to update the template yourself.</li>
</ul>
<?php
echo $HTML->openForm(array('action' => '/tracker/admin/?group_id='.$group_id.'&atid='.$ath->getID(), 'method' => 'post'));
?>
<input type="hidden" name="update_template" value="y" />
<p><?php echo $params['content']; ?></p>
<p><input type="submit" name="post_changes" value="<?php echo _('Submit') ?>" /></p>
<?php
echo $HTML->closeForm();
$ath->footer();

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
