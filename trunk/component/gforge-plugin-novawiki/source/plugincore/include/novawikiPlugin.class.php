<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */
?>
<?php
/**
 * GForge Plugin novawiki Class
 *
 * This file is part of gforge-plugin-novawiki
 *
 */


class novawikiPlugin extends Plugin
{

   function novawikiPlugin ()
   {
      $this->Plugin ();
      $this->name = "novawiki";
      $this->hooks [] = "groupmenu";
      $this->hooks [] = "groupisactivecheckbox";
      $this->hooks [] = "groupisactivecheckboxpost";
   }

   function activatePlugin (&$Group)
   {
      $retour = true;
      
      return $retour;
   }

   function desactivatePlugin (&$Group)
   {
   }

   /**
    * The function to be called for a Hook
    *
    * @param    String  $hookname  The name of the hookname that has been happened
    * @param    String  $params    The params of the Hook
    *
    */
   function CallHook ($hookname, $params)
   {
      global $group_id, $Language, $G_SESSION, $HTML;

      $use_novafrs = getIntFromRequest ('use_novawiki');
      if ($hookname == "groupmenu")
      {
         $project = &group_get_object($group_id);
         if (!$project || !is_object($project))
            return;
         if ($project->isError())
            return;
         if (!$project->isProject())
            return;
         if ($project->usesPlugin ($this->name))
         {
            $params['DIRS'][]='/plugins/novawiki/?group_id='.$group_id;
            $params['TITLES'][]=dgettext('gforge-plugin-novawiki','tab_title');
         }
         (($params['toptab'] == $this->name) ? $params['selected']=(count($params['TITLES'])-1) : '' );
      }
      elseif ($hookname == "groupisactivecheckbox")
      {
         // Check if the group is active
         $group = &group_get_object($group_id);
         echo "<tr><td><input type=\"checkbox\" name=\"use_novawiki\" value=\"1\"";
         // Checked or Unchecked?
         if ($group->usesPlugin ($this->name))
         {
            echo "checked";
         }
         echo "></td><td><strong>" . dgettext ("gforge-plugin-novawiki", "use_novawiki") . "</strong></td></tr>\n";
      }
      elseif ($hookname == "groupisactivecheckboxpost")
      {
         $group = &group_get_object ($group_id);
         if ($use_novafrs == 1)
         {
            $this->activatePlugin ($group);
            $group->setPluginUse ($this->name);
         }
         else
         {
            $group->setPluginUse ($this->name, false);
            $this->desactivatePlugin ($group);
         }
      } 
   }

}

?>
