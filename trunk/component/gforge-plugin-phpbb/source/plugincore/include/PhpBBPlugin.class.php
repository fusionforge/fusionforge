<?php

require_once('common/novaforge/api/SessionApi.class');
require_once ('plugins/phpbb/common/PluginPhpBBDataHandler.class');
require_once ('plugins/phpbb/common/PluginPhpBB.class');
require_once ('plugins/phpbb/common/PluginPhpBBIHM.class');

class PhpBBPlugin extends Plugin {

    function PhpBBPlugin () {
        $this->Plugin() ;
        $this->name = "phpbb" ;
        $this->text = "PhpBB" ;        // To show in the tabs, use...
        $this->hooks[] = "groupmenu";    // To put into the project tabs
        $this->hooks[] = "groupisactivecheckbox";// The "use ..." checkbox in editgroupinfo
        $this->hooks[] = "groupisactivecheckboxpost";
        $this->hooks[] = "project_admin_plugins";// to show up in the admin page fro group
        $this->hooks[] = "session_before_login";// To put into the project tabs
        $this->hooks[] = "before_logout_redirect"; // To destroy sesssion
        $this->hooks[] = "mypage"; // To display my page 
    }

    function CallHook ($hookname, $params) {
        global $use_phpbb_plugin,$Language,$G_SESSION,$HTML;
        $group_id = (isset($params['group']))?$params['group']:0;
      
        switch ($hookname) {
            case "session_before_login":
                $loginname = $params['loginname'];
                $passwd = $params['passwd'];
                 
                $sessionApi = new SessionApi();
                $sessionApi->setUserInSession($loginname, $passwd);
                break;
            case "before_logout_redirect":
                $sessionApi = new SessionApi();
                $sessionApi->destroySession();
                break;
            case "groupmenu":
                $project = &group_get_object($group_id);
                if (!$project || !is_object($project))
                return;
                if ($project->isError())
                return;
                if (!$project->isProject())
                return;
                if ( $project->usesPlugin ($this->name) ) {

                    PluginPhpBBDataHandler::getPhpBBInstances($group_id,$instances,$urls,$name);
                    for($i=0;$i<count($urls);$i++){
                        $params['DIRS'][]   = '/plugins/phpbb/access.php/' . $project->getID() .'/'.$instances[$i]. '/?gconnect';
                        $params['TITLES'][] = $name[$i];
                        (($params['toptab'] == $name[$i]) ? $params['selected']=(count($params['TITLES'])-1) : '' );
                    }

                }

                break;
            case "groupisactivecheckbox":
                //Check if the group is active
                $group = &group_get_object($group_id);
                echo "<tr>";
                echo "<td>";
                echo ' <input type="CHECKBOX" name="use_phpbb_plugin" value="1" ';
                // CHECKED OR UNCHECKED?
                if ( $group->usesPlugin ( $this->name ) ) {
                    echo "CHECKED";
                }
                echo "><br/>";
                echo "</td>";
                echo "<td>";
                echo "<strong>".dgettext('gforge-plugin-phpbb','active_plugin')." </strong>";
                echo "</td>";
                echo "</tr>";
                break;
            case "groupisactivecheckboxpost":
                $group = &group_get_object($group_id);
                if ( $use_phpbb_plugin == 1 ) {
                    $group->setPluginUse ( $this->name );
                } else {
                    $group->setPluginUse ( $this->name, false );
                }
                break;
            case "project_admin_plugins":
                global $Language;
                // this displays the link in the project admin options page to its  Mantis administration
                $group_id = $params['group_id'];
                $group = &group_get_object($group_id);
                if ( $group->usesPlugin ( $this->name ) ) {

                    echo "<a href=\"/plugins/phpbb/admin.php?group_id=" . $group->getID() . "\">". dgettext('gforge-plugin-phpbb','administration_phpbb') .'</a><br />';
                }
                break;
            case "mypage":
                $user_id = $params['user_id'];
                $arr_bookmarks = PluginPhpBB::getBookmarks($user_id);
                PluginPhpBBIHM::display_bookmarks($arr_bookmarks);
                break;
            default:
                break;
        }
    }

}

?>
