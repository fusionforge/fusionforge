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
require_once('common/rss/RSS.class.php');

/**
* Widget_MyProjects
* 
* PROJECT LIST
*/
class Widget_MyProjects extends Widget {
    function Widget_MyProjects() {
        $this->Widget('myprojects');
    }
    function getTitle() {
        return _("My Projects");
    }
    function getContent() {
        $html_my_projects = '';
        $result = db_query_params("SELECT groups.group_name,"
            . "groups.group_id,"
            . "groups.unix_group_name,"
            . "groups.status,"
            . "groups.is_public,"
            . "user_group.admin_flags "
            . "FROM groups,user_group "
            . "WHERE groups.group_id=user_group.group_id "
            . "AND user_group.user_id=$1" 
            . "AND groups.status='A' ORDER BY group_name",array(UserManager::instance()->getCurrentUser()->getID() ));
        $rows=db_numrows($result);
        if (!$result || $rows < 1) {
            $html_my_projects .= _("You're not a member of any project");
            $html_my_projects .= db_error();
        } else {
            
            $html_my_projects .= '<table style="width:100%">';
            for ($i=0; $i<$rows; $i++) {
		    if ($i % 2 == 0) {
					$class="boxitemalt bgcolor-white";
				} else {
					$class="boxitem bgcolor-grey";
				}

                $html_my_projects .= '
                    <TR class="'. $class .'"><TD WIDTH="99%">'.
                    '<A href="/projects/'. db_result($result,$i,'unix_group_name') .'/">'.
                    db_result($result,$i,'group_name') .'</A>';
                if (strpos(db_result($result,$i,'admin_flags'),  'A')==0 ) {
                    $html_my_projects .= ' <small><A HREF="/project/admin/?group_id='.db_result($result,$i,'group_id').'">['._("Admin").']</A></small>';
                }
                if ( db_result($result,$i,'is_public') == 0 ) {
                    $html_my_projects .= ' (*)';
                    $private_shown = true;
                }
                if (strpos(db_result($result,$i,'admin_flags') , 'A')==0 ) {
                    $html_my_projects .= '</td><td>&nbsp;</td></TR>';
                } else {
                    $html_my_projects .= '</TD>'.
                    '<td><A href="rmproject.php?group_id='. db_result($result,$i,'group_id').
                    '" onClick="return confirm(\''._("Quit this project?").'\')">'.
                    '<IMG SRC="'.$GLOBALS['HTML']->imgroot.'ic/trash.png" HEIGHT="16" WIDTH="16" BORDER="0"></A></TD></TR>';
                }
            }
            
            if (isset($private_shown) && $private_shown) {
                $html_my_projects .= '
                <TR class="'.$class .'"><TD colspan="2" class="small">'.
                '(*)&nbsp;'._("<em>Private project</em>").'</td></tr>';
            }
            $html_my_projects .= '</table>';
        }
        return $html_my_projects;
    }
    function hasRss() {
        return true;
    }
    function displayRss() {
        $rss = new RSS(array(
            'title'       => 'Codendi - MyProjects',
            'description' => 'My projects',
            'link'        => get_server_url(),
            'language'    => 'en-us',
            'copyright'   => 'Copyright Xerox',
            'pubDate'     => gmdate('D, d M Y G:i:s',time()).' GMT',
        ));
        $result = db_query_params("SELECT groups.group_name,"
            . "groups.group_id,"
            . "groups.unix_group_name,"
            . "groups.status,"
            . "groups.is_public,"
            . "user_group.admin_flags "
            . "FROM groups,user_group "
            . "WHERE groups.group_id=user_group.group_id "
            . "AND user_group.user_id=$1"
            . "AND groups.status='A' ORDER BY group_name",array(UserManager::instance()->getCurrentUser()->getID() ));
        $rows=db_numrows($result);
        if (!$result || $rows < 1) {
            $rss->addItem(array(
                'title'       => 'Error',
                'description' => _("You're not a member of any project") . db_error(),
                'link'        => util_make_url()
            ));
        } else {
            for ($i=0; $i<$rows; $i++) {
                $title = db_result($result,$i,'group_name');
                if ( db_result($result,$i,'is_public') == 0 ) {
                    $title .= ' (*)';
                }
                
                $desc = 'Project: '. util_make_url('/project/admin/?group_id='.db_result($result,$i,'group_id')) ."<br />\n";
                if ( strpos(db_result($result,$i,'admin_flags') , 'A')==0 ) {
                    $desc .= 'Admin: '. util_make_url('/project/admin/?group_id='.db_result($result,$i,'group_id'));
                }
                
                $rss->addItem(array(
                    'title'       => $title,
                    'description' => $desc,
                    'link'        => util_make_url('/projects/'. db_result($result,$i,'unix_group_name'))
                ));
            }
        }
        $rss->display();
    }
    function getDescription() {
        return _("List the projects you belong to. Selecting any of these projects brings you to the corresponding Project Summary page.");
    }
}
?>
