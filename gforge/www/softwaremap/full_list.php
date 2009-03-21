<?php
/*
 * Copyright (C) 2008-2009 Alcatel-Lucent
 *
 * This file is part of FusionForge.
 *
 * FusionForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Gforge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
 * Standard Alcatel-Lucent disclaimer for contributing to open source
 *
 * "The Full List ("Contribution") has not been tested and/or
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


require_once('../env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'include/trove.php';

if (!$sys_use_trove) {
	exit_disabled();
}

$HTML->header(array('title'=>_('Software Map'),'pagename'=>'softwaremap'));

echo ($HTML->subMenu(
		array(
			_('Project Tree'),
			_('Project List')
		),
		array(
			'/softwaremap/trove_list.php',
			'/softwaremap/full_list.php'
		)
	));

$res_grp = db_query("
	SELECT group_id, group_name, unix_group_name, short_description, register_time
	FROM groups
        WHERE status = 'A' AND is_public=1 AND type_id=1 AND register_time > 0 
	ORDER BY group_name ASC
", $TROVE_HARDQUERYLIMIT);
echo db_error();
$querytotalcount = db_numrows($res_grp);
	
// #################################################################
// limit/offset display

// no funny stuff with get vars

if (!isset($page) || !is_numeric($page)) {
	$page = 1;
}

// store this as a var so it can be printed later as well
$html_limit = '<span style="text-align:center;font-size:smaller">';
if ($querytotalcount == $TROVE_HARDQUERYLIMIT){
	$html_limit .= sprintf(_('More than <strong>%1$s</strong> projects in result set.'), $querytotalcount);
}
$html_limit .= sprintf(_('<strong>%1$s</strong> projects in result set.'), $querytotalcount);

// only display pages stuff if there is more to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	$html_limit .= ' Displaying '.$TROVE_BROWSELIMIT.' per page. Projects sorted by alphabetical order.<br />';

	// display all the numbers
	for ($i=1;$i<=ceil($querytotalcount/$TROVE_BROWSELIMIT);$i++) {
		$html_limit .= ' ';
		if ($page != $i) {
			$html_limit .= '<a href="'.$_SERVER['PHP_SELF'];
			$html_limit .= '?page='.$i;
			$html_limit .= '">';
		} else $html_limit .= '<strong>';
		$html_limit .= '&lt;'.$i.'&gt;';
		if ($page != $i) {
			$html_limit .= '</a>';
		} else $html_limit .= '</strong>';
		$html_limit .= ' ';
	}
}

$html_limit .= '</span>';

print $html_limit."<hr />\n";

// #################################################################
// print actual project listings
// note that the for loop starts at 1, not 0
for ($i_proj=1;$i_proj<=$querytotalcount;$i_proj++) { 
	$row_grp = db_fetch_array($res_grp);

	// check to see if row is in page range
	if (($i_proj > (($page-1)*$TROVE_BROWSELIMIT)) && ($i_proj <= ($page*$TROVE_BROWSELIMIT))) {
		$viewthisrow = 1;
	} else {
		$viewthisrow = 0;
	}	

	if ($row_grp && $viewthisrow) {
		print '<table border="0" cellpadding="0" width="100%">';
		print '<tr valign="top"><td colspan="2">';
		print "<a href=\"/projects/". strtolower($row_grp['unix_group_name']) ."/\"><strong>"
			.$row_grp['group_name']."</strong></a> ";

		if ($row_grp['short_description']) {
			print "- " . $row_grp['short_description'];
		}

		// extra description
		print '</td></tr><tr valign="top"><td>';
		// list all trove categories
		print trove_getcatlisting($row_grp['group_id'],0,1);
		print '</td>';
		print '<td align="right"><br />Register Date: <strong>'.date(_('Y-m-d H:i'),$row_grp['register_time']).'</strong></td>';
		print '</tr>';
/*
                if ($row_grp['jobs_count']) {
                	print '<tr><td colspan="2" align="center">'
                              .'<a href="/people/?group_id='.$row_grp['group_id'].'">[This project needs help]</a></td></td>';
                }
*/
                print '</table>';
		print '<hr />';
	} // end if for row and range chacking
}

// print bottom navigation if there are more projects to display
if ($querytotalcount > $TROVE_BROWSELIMIT) {
	print $html_limit;
}

$HTML->footer(array());

?>
