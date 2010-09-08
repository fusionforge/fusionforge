<?php

/*
 * Copyright 2010, Capgemini
 * Authors: Franck Villaume - capgemini
 *          Antoine Mercadal - capgemini
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
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA
 */

echo '<form id="mainform" method="post" action="?type=group&id='.$id.'&pluginname=mantisbt&view=roadmap">';

$listVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $idProjetMantis));
if ( sizeof($listVersions) ) {
    echo '<div style="float: left;margin-right: 10px; width: 145px; height: 140px;">';
    echo '<h4 style="border-bottom: 1px solid #DAE0EA">Versions :</h4>';
    echo '<select name="projectVersionId[]" id="projectVersionId" multiple style="width: 145px; height: 100px">';
    foreach ( $listVersions as $key => $version ) {
        echo '<option value="'.$version->id.'"';
        if (isset($_POST['projectVersionId'])) {
            $flipped_projectVersionId = array_flip($_POST['projectVersionId']);
            if (isset($flipped_projectVersionId[$version->id])) {
                echo 'selected="selected"';
            }
        }
        echo '>'.$version->name;
        if ( $version->released ) {
            echo '(<i>release</i>)';
        } else {
            echo '(<i>milestone</i>)';
        }
        echo '</option>';
    }
    echo '</select>';
    echo '</div>';
}

if ( sizeof($listChild)) {
    // nous avons deja un bloc
    $nbblock = 1;
    foreach ( $listChild as $key => $child ) {
        $listChildVersions = $clientSOAP->__soapCall('mc_project_get_versions', array("username" => $username, "password" => $password, "project_id" => $child));
        if ( sizeof($listChildVersions)) {
            $resultGroupNameFusionForge = db_query_params ('select groups.group_name from groups,group_mantisbt where groups.group_id = group_mantisbt.id_group and group_mantisbt.id_mantisbt = $1',
                                                           array($child));
            $rowGroupNameFusionForge =& db_fetch_array($resultGroupNameFusionForge);
            echo '<div id="childVersion'.$child.'" style="float: left;margin-right: 10px; width: 145px; height: 140px;">';
            echo '<h4 style="border-bottom: 1px solid #DAE0EA; width: 145px;">'.$rowGroupNameFusionForge['group_name'].'</h4>';
            echo '<select name="project'.$child.'VersionId[]" id="project'.$child.'VersionId" multiple style="width: 145px; height: 100px">';
            foreach ( $listChildVersions as $key => $version ) {
                echo '<option value="'.$version->id.'"';
                if (isset($_POST['project'.$child.'VersionId'])) {
                    $flipped_projectVersionId = array_flip($_POST['project'.$child.'VersionId']);
                    if (isset($flipped_projectVersionId[$version->id])) {
                        echo 'selected="selected"';
                    }
                }
                echo '>'.$version->name;
                if ( $version->released ) {
                    echo '(<i>release</i>)';
                } else {
                    echo '(<i>milestone</i>)';
                }
                echo '</option>';
            }
            echo '</select>';
            echo '</div>';
            $nbblock++;
            if ( $nbblock == 7 ) {
                echo '<div style="clear:both; width:100%"></div>';
		$nbblock = 0;
            }
        }
    }
}

echo <<< EOT
    <script>
        function reinit()
        {
            if (document.getElementById("projectVersionId"))
                document.getElementById("projectVersionId").selectedIndex = -1;
EOT;
if ( sizeof($listChild)) {
    foreach ( $listChild as $key => $child ) {
        echo 'if (document.getElementById("project'.$child.'VersionId"))
                document.getElementById("project'.$child.'VersionId").selectedIndex = -1;';
    }
}
echo <<< EOT

            document.getElementById("mainform").submit();
        }
    </script>
EOT;
echo '<br/><div style="clear:both;width:100%; text-align: right">'.
     '<input type="button" value="Reinitialiser" onclick="reinit();"/> '.
     '<input type="submit" value="Appliquer le filtre" /></div>';
echo '</form>';

?>
