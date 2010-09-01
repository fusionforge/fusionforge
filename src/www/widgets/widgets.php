<?php
/*
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
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once('../env.inc.php');
require_once $gfcommon.'include/pre.php';
require_once $gfcommon.'include/preplugins.php';
require_once $gfcommon.'include/plugins_utils.php';
//require_once('www/my/my_utils.php');
require_once $gfcommon.'widget/WidgetLayoutManager.class.php';
require_once $gfcommon.'widget/Valid_Widget.class.php';

$hp = Codendi_HTMLPurifier::instance();
if (isLogged()) {
    
    $request =& HTTPRequest::instance();
    $lm = new WidgetLayoutManager();
    $vLayoutId = new Valid_UInt('layout_id');
    $vLayoutId->required();
    if ($request->valid($vLayoutId)) {
        $layout_id = $request->get('layout_id');

        $vOwner = new Valid_Widget_Owner('owner');
        $vOwner->required();
        if ($request->valid($vOwner)) {
            $owner = $request->get('owner');
            $owner_id   = (int)substr($owner, 1);
            $owner_type = substr($owner, 0, 1);
            switch($owner_type) {
                case WidgetLayoutManager::OWNER_TYPE_USER:
                    $owner_id = user_getid();

		    		$userm=UserManager::instance();
		    		$current=$userm->getCurrentUser();
		    		echo site_user_header(array('title'=>sprintf(_('Personal Page For %s'),user_getname())));
 					use_javascript('/scripts/codendi/LayoutManager.js');
                   //my_header(array('title'=>$title, 'selected_top_tab' => '/my/'));
                    $lm->displayAvailableWidgets(user_getid(), WidgetLayoutManager::OWNER_TYPE_USER, $layout_id);
                    site_footer(array());
                    
                    break;
                case WidgetLayoutManager::OWNER_TYPE_GROUP:
                    $pm = ProjectManager::instance();
                    if ($project = $pm->getProject($owner_id)) {
                        $group_id = $owner_id;
                        $_REQUEST['group_id'] = $_GET['group_id'] = $group_id;
                        $request->params['group_id'] = $group_id; //bad!
                        if (user_ismember($group_id, 'A') || user_is_super_user()) {
                            $title = _("Project info").' - '. $project->getPublicName();
                            site_project_header(array('title'=>$title,'group'=>$group_id,'toptab'=>'summary'));
							use_javascript('/scripts/codendi/LayoutManager.js');
                            $lm->displayAvailableWidgets($group_id, WidgetLayoutManager::OWNER_TYPE_GROUP, $layout_id);
                            site_footer(array());
                        } else {
                            $GLOBALS['Response']->redirect('/projects/'.$project->getUnixName().'/');
                        }
                    }
                    break;
                default:
                    break;
            }
        }
    }
} else {
    exit_not_logged_in();
}
?>
