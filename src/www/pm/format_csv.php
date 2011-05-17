<?php
/*
 * Copyright (C) 2009 Alain Peyrat, Alcatel-Lucent
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

/*
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


pm_header(array('title'=>_('Upload data into the tasks.'),'group_project_id'=>$group_project_id));

$headers = getIntFromRequest('headers', 1);
$full = getIntFromRequest('full', 1);
$sep = getStringFromRequest('sep', ',');

?>
<center>
<table>
	<tr>
		<td>
		<fieldset><legend><b>CSV Format</b></legend>
		<form action="/pm/task.php" method="get"><input type="hidden"
			name="group_id" value="<?php echo $group_id ?>" /> <input
			type="hidden" name="group_project_id"
			value="<?php echo $group_project_id ?>" /> <input type="hidden"
			name="func" value="csv" />
		<table>
			<tr>
				<td valign="top"><b>Content :</b></td>
				<td><input type="radio" name="full" value="1"<?php if ($full) echo ' checked="checked"' ?>/>Full<br />
				<input type="radio" name="full" value="0"<?php if (!$full) echo ' checked="checked"' ?> />Normal</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr>
				<td valign="top"><b>Separator :</b></td>
				<td><input type="radio" name="sep" value=","<?php if ($sep==',') echo ' checked="checked"' ?>/>Comma (char: ',')<br />
				<input type="radio" name="sep" value=";"<?php if ($sep==';') echo ' checked="checked"' ?>/>Semi-colon (char: ';')</td>
			</tr>
			<tr><td colspan="2">&nbsp;</td></tr>
			<tr>
				<td valign="top"><b>Header :</b></td>
				<td><input type="radio" name="headers" value="1"<?php if ($headers) echo ' checked="checked"' ?>/>Included<br />
				<input type="radio" name="headers" value="0"<?php if (!$headers) echo ' checked="checked"' ?>/>None</td>
			</tr>
		</table>
		<input type="submit" name="Submit" /></form>
		</fieldset>
		</td>
	</tr>
</table>
</center>
<p><strong>Notes:</strong></p>
<div>
<ul>
<li><strong>Full/Normal :</strong> In Full, the category is also exported.</li>
<li><strong>Comma/Semi-colon :</strong> Some international version of MS Excel uses ';' instead of ','.</li>
<li><strong>Headers Included or not :</strong> Add a line with the name of the fields at the fist line.</li>
</ul>
</div>
<?php
pm_footer(array());
?>
