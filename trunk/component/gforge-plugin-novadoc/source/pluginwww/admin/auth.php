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

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("common/include/Role.class.php");
require_once ("plugins/novadoc/include/DocumentGroupAuth.class.php");
require_once ("plugins/novadoc/include/DocumentGroupFactory.class.php");
require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/AuthView.class.php");
require_once ("plugins/novadoc/include/utils.php");

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object ($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$perm =& $g->getPermission( session_get_user() );
if (!$perm || $perm->isError() || !$perm->isDocEditor()) {
	exit_permission_denied();
}



function prepareAuthRecursive( & $nested_groups, & $tabAuth, & $roles, $idDocGroup, $fatherRole ){

    if( ! isset( $nested_groups[ $idDocGroup ] ) ){
        return;
    }
    
    
    foreach( $nested_groups[ $idDocGroup ] as $group ){
        $idGroup =  $group->getID();
        
        $savFatherRole = $fatherRole;

        foreach( $roles as $role ){
            $idRole  =  $role['role_id'];
            
            if( isset( $tabAuth[ $idGroup ][ $idRole ] ) ){
                $auth = $tabAuth[ $idGroup ][ $idRole ];
                $fatherRole[ $idRole ] = $auth; // change father auth for children
            }else{
                if( $fatherRole[ $idRole ] == null ){
                    $tabAuth[ $idGroup ][ $idRole ] = null;
                }else{
                    $tabAuth[ $idGroup ][ $idRole ] = $fatherRole[ $idRole ];
                }
            }
        }
        prepareAuthRecursive( $nested_groups, $tabAuth, $roles, $idGroup, $fatherRole );
        $fatherRole = $savFatherRole;
    }     
}


function prepareAuth( & $nested_groups, & $tabAuth, & $roles ){
    $fatherRole = array();
    
    foreach( $roles as $role ){
        $fatherRole[ $role['role_id'] ] = null;
    }
    
    prepareAuthRecursive( $nested_groups,  $tabAuth,  $roles, 0, $fatherRole );
}



$dgf = new DocumentGroupFactory($g);
if ($dgf->isError()) {
	exit_error(dgettext('general','error'),$dgf->getErrorMessage());
}

// Get the document groups info
$nested_groups =& $dgf->getNested();
$roles = novadoc_getRoles ($group_id);
if( isset( $_POST['auth'] ) ){
    // edit of authorizations
    
    $tabPrepareAuth = $_POST['auth'];
    prepareAuth( $nested_groups, $tabPrepareAuth, $roles );
    
    $ret = DocumentGroupAuth::setAuth( $group_id, $tabPrepareAuth );    
    if( $ret == false ){
        exit_error( 'Error', $ret->getErrorMessage() );
    }
     
    
}

// display authorization form

$groupAuth = DocumentGroupAuth::getAllAuth( $group_id );
if( $groupAuth === false ){
    exit_error( "Can't read authorization" );
}


novadoc_header (dgettext ('gforge-plugin-novadoc','title_admin'));

echo '<table>';
    $authView = new AuthView();
    $authView->printAuthForm( $groupAuth, $nested_groups , $roles );
echo '</table>';

novadoc_footer ();

?>
