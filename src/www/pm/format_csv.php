<?php
/**
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
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

/**
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The provided file ("Contribution") has not been tested and/or
 * validated for release as or in products, combinations with products or
 * other commercial use. Any use of the Contribution is entirely made at
 * the user's own responsibility and the user can not rely on any features,
 * functionalities or performances Alcatel-Lucent has attributed to the
 * Contribution.
 *
 * THE CONTRIBUTION BY ALCATEL-LUCENT IS PROVIDED AS IS, WITHOUT WARRANTY
 * OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, COMPLIANCE,
 * NON-INTERFERENCE AND/OR INTERWORKING WITH THE SOFTWARE TO WHICH THE
 * CONTRIBUTION HAS BEEN MADE, TITLE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * ALCATEL-LUCENT BE LIABLE FOR ANY DAMAGES OR OTHER LIABLITY, WHETHER IN
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * CONTRIBUTION OR THE USE OR OTHER DEALINGS IN THE CONTRIBUTION, WHETHER
 * TOGETHER WITH THE SOFTWARE TO WHICH THE CONTRIBUTION RELATES OR ON A STAND
 * ALONE BASIS."
 */

//
//	This page contains a form with a file-upload button
//	so a user can choose a file to upload a .csv file and store it in task mgr
//

pm_header(array('title'=>_('Update CSV Format'), 'modal'=>1, 'group_project_id'=>$group_project_id));

$headers = getIntFromRequest('headers', 1);
$full = getIntFromRequest('full', 1);
$sep = getStringFromRequest('sep', ',');

?>
<table class="centered">
	<tr>
		<td>
		<fieldset><legend><strong><?php echo _('CSV Format'); ?></strong></legend>
		<form action="/pm/task.php" method="get">
			<input type="hidden" name="group_id" value="<?php echo $group_id ?>" />
			<input type="hidden" name="group_project_id" value="<?php echo $group_project_id ?>" />
			<input type="hidden" name="func" value="csv" />
		<table class="infotable">
			<tr>
				<td>
					<?php echo _('Content')._(':'); ?>
				</td>
				<td>
					<input type="radio" id="full" name="full" value="1"<?php if ($full) echo ' checked="checked"' ?>/>
					<label for="full">
						<?php echo _('Full'); ?><br />
					</label>
					<input type="radio" id="normal" name="full" value="0"<?php if (!$full) echo ' checked="checked"' ?> />
					<label for="normal">
						<?php echo _('Normal'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo _('Separator')._(':'); ?>
				</td>
				<td>
					<input type="radio" id="comma" name="sep" value=","<?php if ($sep==',') echo ' checked="checked"' ?>/>
					<label for="comma">
						<?php echo _('Comma (char: “,”)'); ?><br />
					</label>
					<input type="radio" id="semi-colon" name="sep" value=";"<?php if ($sep==';') echo ' checked="checked"' ?>/>
					<label for="semi-colon">
						<?php echo _('Semi-colon (char: “;”)'); ?>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo _('Header')._(':'); ?>
				</td>
				<td>
					<input type="radio" id="included" name="headers" value="1"<?php if ($headers) echo ' checked="checked"' ?> />
					<label for="included">
						<?php echo _('Included'); ?><br />
					</label>
					<input type="radio" id="none" name="headers" value="0"<?php if (!$headers) echo ' checked="checked"' ?> />
					<label for="none">
						<?php echo _('None'); ?>
					</label>
				</td>
			</tr>
		</table>
		<input type="submit" name="Submit" /></form>
		</fieldset>
		</td>
	</tr>
</table>
<h2><?php echo _('Notes'); ?></h2>
<ul>
<li>
    <strong><?php echo _('Full/Normal')._(':'); ?></strong>
    <?php echo _('In Full, the category is also exported.'); ?>
</li>
<li>
    <strong><?php echo _('Comma/Semi-colon')._(':'); ?></strong>
    <?php echo _('Some international versions of Microsoft Excel use “;” instead of “,”.'); ?>
</li>
<li>
    <strong><?php echo _('Headers Included or not')._(':'); ?></strong>
    <?php echo _('Add a line with the name of the fields at the first line.'); ?>
</li>
</ul>

<?php
pm_footer();
