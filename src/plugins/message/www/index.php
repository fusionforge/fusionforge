<?php
/*
 * Copyright (C) 2011 Alain Peyrat, Alcatel-Lucent
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

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

$message= getStringFromRequest('body');
if ($message) {
	$res = db_query_params('SELECT message FROM plugin_message', array());
	if (!$res || db_numrows($res)==0) {
		db_query_params('INSERT INTO plugin_message (message) VALUES ($1)', array($message));
	} else {
		db_query_params('UPDATE plugin_message SET message=$1', array($message));
	}
} else {
	$res = db_query_params('SELECT message FROM plugin_message', array());
	if ($res && db_numrows($res)>0) {
		$message = db_result($res, 0, 'message');
	}
}

site_admin_header($params);														

print _("Edit the message as you want. If you activate the HTML editor, you will be able to use WYSIWYG formatting (bold, colors...)");

print "<p/><center>";
print "<form action=\"/plugins/message/\" method=\"post\">";

$params['body'] = $message;
$params['width'] = "800";
$params['height'] = "300";
$params['group'] = $id;
$params['content'] = '<textarea name="body"  rows="20" cols="80">'.$message.'</textarea>';
plugin_hook_by_reference("text_editor", $params);
echo $params['content'];

print "<br /><br /><input type=\"submit\" value=\"" ._("Save") ."\" />";
print "</form>";
print "</center>";

site_admin_footer($params);														
