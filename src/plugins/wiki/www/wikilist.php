<?php
/**
 * List of active wikis in Forge
 *
 * Copyright 2009-2011 Marc-Etienne Vargenau, Alcatel-Lucent
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
 * along with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once dirname(__FILE__)."/../../env.inc.php";
require_once $gfcommon.'include/pre.php';
require_once $gfwww.'admin/admin_utils.php';

$title = _('List of active wikis in Forge');
site_admin_header(array('title'=>$title));

$sortorder = getStringFromRequest('sortorder', 'group_name');
$sortorder = util_ensure_value_in_set ($sortorder, array ('group_name','register_time','unix_group_name','is_public','is_external','members')) ;

$res = db_query_params('SELECT group_name,register_time,unix_group_name,groups.group_id,is_public,is_external,status, COUNT(user_group.group_id) AS members
			FROM groups LEFT JOIN user_group ON user_group.group_id=groups.group_id
            WHERE status=$1
            GROUP BY group_name,register_time,unix_group_name,groups.group_id,is_public,is_external,status
            ORDER BY '.$sortorder,
			array('A'));

$headers = array(
    _('Project Name'),
    _('Project Register Time'),
    _('Unix name'),
    _('Public?')
);
if (isset($sys_intranet) & $sys_intranet) {
    $headers[] = _("External?");
}
$headers[] = _('Members');
$headers[] = _('Upgrade');

$headerLinks = array(
    '/wiki/wikilist.php?sortorder=group_name',
    '/wiki/wikilist.php?sortorder=register_time',
    '/wiki/wikilist.php?sortorder=unix_group_name',
    '/wiki/wikilist.php?sortorder=is_public');
if (isset($sys_intranet) & $sys_intranet) {
    $headerLinks[] = '?sortorder=is_external';
}
$headerLinks[] = '/wiki/wikilist.php?sortorder=members';
$headerLinks[] = '';

echo $HTML->listTableTop($headers, $headerLinks);

$i = 0;
while ($grp = db_fetch_array($res)) {

    $project = group_get_object($grp['group_id']);
    if ($project->usesPlugin("wiki")) {
        $time_display = "";
        if ($grp['register_time'] != 0) {
            $time_display = date(_('Y-m-d H:i'),$grp['register_time']);
        }
        echo '<tr '.$HTML->boxGetAltRowStyle($i).'>';
        echo '<td><a href="/wiki/g/'.$grp['unix_group_name'].'/">'.$grp['group_name'].'</a></td>';
        echo '<td>'.$time_display.'</td>';
        echo '<td>'.$grp['unix_group_name']. '</td>';
        echo '<td>'.$grp['is_public'].'</td>';
        if (isset($sys_intranet) & $sys_intranet) {
            echo '<td>'.$grp['is_external'].'</td>';
        }
        echo '<td>'.$grp['members'].'</td>';
        echo '<td><a href="/wiki/g/'.$grp['unix_group_name'].'/?action=upgrade">'._("Upgrade").'</a></td>';
        echo '</tr>';
        $i++;
    }
}

echo $HTML->listTableBottom();

site_admin_footer(array());

?>
