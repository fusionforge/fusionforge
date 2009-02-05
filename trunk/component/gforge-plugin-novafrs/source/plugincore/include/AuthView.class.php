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

class AuthView{
    
    function getRoleTxt( $idAuth ){
        global $Language;
        $nameAuth = array(
            0 => ' &nbsp; ',
            1 => dgettext('gforge-plugin-novafrs','authLabel1'),
            2 => dgettext('gforge-plugin-novafrs','authLabel2'),
            3 => dgettext('gforge-plugin-novafrs','authLabel3'),
            4 => dgettext('gforge-plugin-novafrs','authLabel4'),
        );
        return $nameAuth[ $idAuth ];    
    }
    
    function printJS(){
        global $Language;
        ?>
        <script language="JavaScript" type="text/JavaScript">
            function propagateAuth( first, last, role ){ 
                if( first == last ) return true;
    
                if( !confirm( '<?php echo addslashes( dgettext('gforge-plugin-novafrs','propagateAuth') ); ?>' ) ){
                    return false;
                }
                
                var elt = document.getElementById( 'selectAuth' + first + '_' + role );
                var val = elt.value;
                
                for( i=first+1; i<last+1; i++ ){
                    var eltChild = document.getElementById( 'selectAuth' + i + '_' + role );
                    eltChild.value = val;
                }
                return true;
            }
        </script>
        <?php
    }
    
    function printCSS(){
        $config = FileConfig::getInstance();
        ?>
            <style type="text/css">
                .auth1{
                    background-color: <?php echo $config->authColor1; ?>; 
                }
                .auth2{
                    background-color: <?php echo $config->authColor2; ?>;
                }
                .auth3{
                    background-color: <?php echo $config->authColor3; ?>;
                }
                .auth4{
                    background-color: <?php echo $config->authColor4; ?>;
                }
            </style>
        <?php
    }
    
    function getHtmlAuthChoice( $nameSelect, $auth, $uniqueId, $endChildId, $roleId, $isDefault = false ){
        $html = '';
        
        $selected = array_fill( 1, 5, '' );
        $selected[ $auth ] = ' selected="selected" ';
        
        $style[ 1 ] = ' class="auth1" ';
        $style[ 2 ] = ' class="auth2" ';
        $style[ 3 ] = ' class="auth3" ';
        $style[ 4 ] = ' class="auth4" ';
        
        $textAuth = array();
        for($i=1;$i<5;$i++){
            if( $isDefault and $auth == $i ){
                $textAuth[$i] = '*'  . $this->getRoleTxt($i);
            }else{
                $textAuth[$i] = $this->getRoleTxt($i);
            }
        }
    
        $html .= "\n<select name=\"$nameSelect\" id=\"selectAuth".$uniqueId."_".$roleId."\" "
                . " onChange=\"propagateAuth( $uniqueId, $endChildId, $roleId );\"  > " ;
                for($i=1;$i<5;$i++){
                    $html .= '<option value="' . $i .'"' . $selected[$i] . $style[$i] . '>'
                                . $textAuth[$i] . '</option>' . "\n";
                }
        $html .= '</select>';
        return $html;
    }
    
    
    function getChildrenCount( & $nested_groups, $idFrGroup ){
        if( ! isset( $nested_groups[ $idFrGroup ] ) ){
            return 0;
        }
        
        $count = 0;
        foreach( $nested_groups[ $idFrGroup ] as $group ){
            $count += 1 + $this->getChildrenCount( $nested_groups, $group->getID() );
        } 
        return $count;
    
    }
    
    
    function getHtmlAuthFormRecursive( & $groupAuth, & $nested_groups, & $roles, & $defaultAuthRole, $fatherRole, $idFrGroup=0, $depth=0  ){
        $html = '';
        static $uniqueId = 0;
        static $countTR = 0;
            
        if( ! isset( $nested_groups[ $idFrGroup ] ) ){
            return;
        }
        
        $config = FileConfig::getInstance();
        
        foreach( $nested_groups[ $idFrGroup ] as $group ){
            $idGroup =  $group->getID();
            
            $savFatherRole = $fatherRole;
            if( ( $countTR++ ) % 2 ){
                $html .= '<tr ' . $GLOBALS['HTML']->boxGetAltRowStyle(0) . '>';
            }else{
                $html .= '<tr>';
            }
            $html .= '<td style="text-align:left;padding-left:' . $depth*$config->decalage/2 . 'px;" > ';
            $html .= '<img src="' . $config->imgRepF . '" /> ';
            $html .= $group->getName() . '</td>';
            $uniqueId++;
            $endChildId = $uniqueId + $this->getChildrenCount( $nested_groups, $group->getID() );
            
            foreach( $roles as $role ){
                $html .= '<td width="80px;"> ';
                $idRole  =  $role['role_id'];
                $nameSelect= "auth[$idGroup][$idRole]";
                
                if( isset( $groupAuth[ $idGroup ][ $idRole ] ) ){
                    $auth = $groupAuth[ $idGroup ][ $idRole ]['auth'];
                    
                    $html .= $this->getHtmlAuthChoice( $nameSelect,  $auth, $uniqueId, $endChildId, $idRole );
                    $fatherRole[ $idRole ] = $auth; // change father auth for children
                }else{
                    if( $fatherRole[ $idRole ] == null ){
                        $html .= $this->getHtmlAuthChoice( $nameSelect, $defaultAuthRole[$idRole], $uniqueId, $endChildId, $idRole, true );
                    }else{
                        $html .= $this->getHtmlAuthChoice( $nameSelect, $fatherRole[$idRole], $uniqueId, $endChildId, $idRole );
                    }
                }
                $html .= '</td>';
            }
            $html .= '</tr>';
            $html .= $this->getHtmlAuthFormRecursive( $groupAuth, $nested_groups, $roles, $defaultAuthRole, $fatherRole, $group->getID(), $depth+1 );
            $fatherRole = $savFatherRole;
        }     
    
        return $html;       
           
    }
    
    
    function printAuthForm( & $groupAuth, & $nested_groups, & $roles ){
        $this->printJS();
        $this->printCSS();
        global $Language;
        $defaultAuthRole = array();
        $fatherRole = array();
        
        foreach( $roles as $role ){
            $defaultAuthRole[  $role['role_id'] ] = FileGroupAuth::getDefaultAuthByRoleName( $role['role_name'] );
            $fatherRole[ $role['role_id'] ] = null;
        }
        ?>
        <form action="" method="post">
        <table border="1">
            <tr>
                <th style="text-align:right"> Role: </th>
                <?php foreach( $roles as $role ): ?>
                    <th> <?php  echo $role['role_name']; ?> </th>
                <?php endforeach; ?>
            </tr>
            <!--
            <tr>
                <td style="text-align:right"> Auth. par defaut : </td>
                <?php foreach( $roles as $role ): ?>
                    <td> <?php  echo $this->getRoleTxt( FileGroupAuth::getDefaultAuthByRoleName( $role['role_name'] ) ); ?> </td>
                <?php endforeach; ?>
            </tr>
            -->
        <?php   
            echo $this->getHtmlAuthFormRecursive( $groupAuth, $nested_groups, $roles, $defaultAuthRole, $fatherRole );
        ?>
        </table>
        <p style="text-align:left;">
        <?php echo dgettext('gforge-plugin-novafrs','infoDefaultAuth'); ?> <br /> <br />
        <input type="submit" value="<?php echo dgettext('gforge-plugin-novafrs','submit'); ?>">
        </p>
        </form>
        <?php    
    }
    
}


?>
