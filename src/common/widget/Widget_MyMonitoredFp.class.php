<?php
/**
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */

require_once('Widget.class.php');
require_once('common/frs/FRSPackageFactory.class.php');
/**
* Widget_MyMonitoredFp
*
* Filemodules that are actively monitored
*/
class Widget_MyMonitoredFp extends Widget {
    function Widget_MyMonitoredFp() {
        $this->Widget('mymonitoredfp');
    }
    function getTitle() {
        return _("Monitored File Packages");
    }
    function getContent() {
        $frsrf = new FRSReleaseFactory();
        $html_my_monitored_fp = '';
        $sql="SELECT groups.group_name,groups.group_id ".
            "FROM groups,filemodule_monitor,frs_package ".
            "WHERE groups.group_id=frs_package.group_id ".
            "AND frs_package.status_id !=$1".
            "AND frs_package.package_id=filemodule_monitor.filemodule_id ".
            "AND filemodule_monitor.user_id=$2";
        $um =& UserManager::instance();
        $current_user =& $um->getCurrentUser();
        if ($current_user->isRestricted()) {
            $projects = $current_user->getProjects();
            $sql .= "AND groups.group_id IN (". implode(',', $projects) .") ";
        }
        $sql .= "GROUP BY group_id ORDER BY group_id ASC LIMIT 100";

        $result=db_query_params($sql,array($frsrf->STATUS_DELETED,user_getid()));
        $rows=db_numrows($result);
        if (!$result || $rows < 1) {
            $html_my_monitored_fp .= '<p><b>' . _("You are not monitoring any files") . '</b></p><p>' . _("If you monitor files, you will be sent new release notices via email, with a link to the new file on our download server.") . '</p><p>' . _("You can monitor files by visiting a project's &quot;Summary Page&quot; and clicking on the appropriate icon in the files section.") . '</p>';
        } else {
            $html_my_monitored_fp .= '<table style="width:100%">';
            $request =& HTTPRequest::instance();
            for ($j=0; $j<$rows; $j++) {
                $group_id = db_result($result,$j,'group_id');

                $sql2="SELECT frs_package.name,filemodule_monitor.filemodule_id ".
                    "FROM groups,filemodule_monitor,frs_package ".
                    "WHERE groups.group_id=frs_package.group_id ".
                    "AND groups.group_id=$1 ".
                    "AND frs_package.status_id !=$2".
                    "AND frs_package.package_id=filemodule_monitor.filemodule_id ".
                    "AND filemodule_monitor.user_id=$3  LIMIT 100";
                $result2 = db_query_params($sql2,array($group_id,$frsrf->STATUS_DELETED,user_getid()));
                $rows2 = db_numrows($result2);

                $vItemId = new Valid_UInt('hide_item_id');
                $vItemId->required();
                if($request->valid($vItemId)) {
                    $hide_item_id = $request->get('hide_item_id');
                } else {
                    $hide_item_id = null;
                }

                $vFrs = new Valid_WhiteList('hide_frs', array(0, 1));
                $vFrs->required();
                if($request->valid($vFrs)) {
                    $hide_frs = $request->get('hide_frs');
                } else {
                    $hide_frs = null;
                }

                list($hide_now,$count_diff,$hide_url) = my_hide_url('frs',$group_id,$hide_item_id,$rows2,$hide_frs);

                $html_hdr = ($j ? '<tr class="boxitem"><td colspan="2">' : '').
                    $hide_url.'<a href="/project/?group_id='.$group_id.'">'.
                    db_result($result,$j,'group_name').'</a>    ';

                $html = '';
                $count_new = max(0, $count_diff);
                for ($i=0; $i<$rows2; $i++) {
                    if (!$hide_now) {
                        $html .='
                        <tr class="'. util_get_alt_row_color($i) .'">'.
                            '<td width="99%">    - <a href="/file/showfiles.php?group_id='.$group_id.'">'.
                            db_result($result2,$i,'name').'</a></td>'.
                            '<td><a href="/file/filemodule_monitor.php?filemodule_id='.
                            db_result($result2,$i,'filemodule_id').
                            '" onClick="return confirm(\''._("Stop Monitoring this Package?").'\')">'.
                            '<img src="'.util_get_image_theme("ic/trash.png").'" height="16" width="16" '.
                            'border="0" alt="'._("STOP MONITORING").'" /></a></td></tr>';
                    }
                }

                $html_hdr .= my_item_count($rows2,$count_new).'</td></tr>';
                $html_my_monitored_fp .= $html_hdr .$html;
            }
            $html_my_monitored_fp .= '</table>';
        }
        return $html_my_monitored_fp;
    }

    function getCategory() {
        return 'frs';
    }
    function getDescription() {
        return _("List packages that you are currently monitoring, by project.<br />To cancel any of the monitored items just click on the trash icon next to the item label.");
    }
    function isAjax() {
        return true;
    }
}
?>
