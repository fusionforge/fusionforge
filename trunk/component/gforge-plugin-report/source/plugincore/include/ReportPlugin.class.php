<?php

require_once ("common/include/Error.class.php");
require_once ("common/include/Plugin.class.php");
require_once ("common/novaforge/log.php");

class ReportPlugin extends Plugin
{

    function ReportPlugin ()
    {
        $this->Plugin ();
        $this->name = "report";
        $this->hooks[] = "groupmenu";
        $this->hooks[] = "groupisactivecheckbox";
        $this->hooks[] = "groupisactivecheckboxpost";
    }
    
    /**
    * The function to be called for a Hook
    *
    * @param    String  $hookname  The name of the hookname that has been happened
    * @param    String  $params    The params of the Hook
    *
    */
    function CallHook ($hookname, $params){    
        global $Language,
            $HTML,
            $G_SESSION;
        switch($hookname){
        
            case "groupmenu" :
            
                $group = &group_get_object ($params ["group"]);
                if ((isset ($group) !== false) &&  (is_object ($group) == true) &&  ($group->isError () == false) &&  ($group->isProject () == true)){
                    if ($group->usesPlugin ($this->name) == true){
                        $params ['DIRS'] [] = "/plugins/report/?group_id=" . $group->getID ();
                        $params ['TITLES'] [] = dgettext ("gforge-plugin-report", "tab_title");
                    }
                    if ($params ["toptab"] == $this->name){
                        $params ["selected"] = count ($params ["TITLES"]) - 1;
                    }
                }
                break;
                
            case "groupisactivecheckbox" :
            
                $group = &group_get_object ($params ["group"]);
                echo "<tr><td><input type=\"checkbox\" name=\"use_report\" value=\"1\"";
                if ($group->usesPlugin ($this->name) == true){
                    echo " checked";
                }
                echo "></td><td><strong>". dgettext ("gforge-plugin-report", "use_report") ."</strong></td></tr>\n";
                break;
                
            case "groupisactivecheckboxpost" :
                
                $group = &group_get_object ($params ["group"]);
                if (getIntFromRequest ("use_report") == 1){
                    $group->setPluginUse ($this->name, true);
                } else {
                    $group->setPluginUse ($this->name, false);
                }
                header("Location: ".$GLOBALS['sys_urlprefix']."/project/admin/editgroupinfo.php?group_id=" . $params ["group"]);
                break;
        }
    }

}

?>


