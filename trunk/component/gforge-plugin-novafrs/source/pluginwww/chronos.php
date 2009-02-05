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
require_once ("plugins/novafrs/include/File.class.php");
require_once ("plugins/novafrs/include/FileConfig.class.php");
require_once ("plugins/novafrs/include/FileGroupAuth.class.php");
require_once ("plugins/novafrs/include/utils.php");

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object ($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$config = FileConfig::getInstance();

novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_display'));

$d = new File($g);
$chronos = $d->getChronoTable();


function getInfo( $idInfo, &$fr ){
    global $sys_datefmt, $Language;
    
    switch( $idInfo ){
        case "chrono" :
            $ret = $fr->getChrono();
            break;
        case "title" :
            $link = (( $fr->isURL() ) ? $fr->getFileName() : "view.php/".$fr->Group->getID()."/".$fr->getID()."/". urlencode(novafrs_unixString($fr->getFileName())) );
            $link_edit = '/plugins/novafrs/card.php?group_id='.$fr->Group->getID() . '&frid=' . $fr->getID();
            
            $ret =  '<a href="'.$link.'">'. $fr->getName() . '</a>'.
                    ' <a href="'.$link_edit.'">('.dgettext('gforge-plugin-novafrs','viewCard').')</a>';

            break;
        case "author" :
            $ret = $fr->getAuthor();
            break;
        case "description" :
            $ret = $fr->getDescription();
            break;
        case "writingDate" :
            $ret = $fr->getWritingDate();
            break;
        case "updatedate" :
            $datestamp = $fr->getUpdateDate();
            if( $datestamp == 0 ) $datestamp = $fr->getCreateDate();
            $date_string = date($sys_datefmt, $datestamp );
            $ret = $date_string;
            break;
        case "version" :
            $ret = $fr->getVersion();
            break;
        case "type" :
            $ret = $fr->getFrType();
            break;
        case "reference" :
            $ret = $fr->getReference();
            break;
        case "status" :
            if( $fr->isDeleted() ){
                $ret = dgettext('gforge-plugin-novafrs','deleted');
            }else{
                $config = FileConfig::getInstance(); 
                $ret = isset( $config->statusText[$fr->getStatus()] )? $config->statusText[$fr->getStatus()] : '?';
            }
            break;
    }
    if( $ret == '' ) $ret = '&nbsp;';
    return $ret;
}


function getInfoHeader( $idInfo ){
    global $Language;
    switch( $idInfo ){
        case "chrono" :
            return dgettext('gforge-plugin-novafrs','chrono');
            break;
        case "title" :
            return dgettext('gforge-plugin-novafrs','title');
            break;
        case "author" :
            return dgettext('gforge-plugin-novafrs','author');
            break;
        case "description" :
            return dgettext('gforge-plugin-novafrs','description');
            break;
        case "writingDate" :
            return dgettext('gforge-plugin-novafrs','writingDate');
            break;
        case "updatedate" :
            return dgettext('gforge-plugin-novafrs','updateDate');
            break;
        case "version" :
            return dgettext('gforge-plugin-novafrs','version');
            break;
        case "type" :
            return dgettext('gforge-plugin-novafrs','frtype');
            break;
        case "reference" :
            return dgettext('gforge-plugin-novafrs','reference');
            break;
        case "status" :
            return dgettext('gforge-plugin-novafrs','status');
            break;
        default : return "bad id: $idInfo ";
    }
}

$chronoTable = $config->chronoTable;

$auth = new FileGroupAuth( $group_id, $LUSER );

?>
<script type="text/javascript" src="sorttable.js"></script>
<table class="sortable" id="tableChronos" border="1">
    <tr>
        <?php
            foreach( $chronoTable as $idHeader => $width  ){
                echo '<th width="',$width,'">', getInfoHeader( $idHeader ), '</th>';
            }
         ?>
    </tr>
<?php foreach( $chronos as $fr ): ?>
    <?php 
        if( !$auth->canRead( $fr->getFrGroupID() ) ) continue;
    ?>
    <tr>
        <?php
            foreach( $chronoTable as $idHeader => $width  ){
                echo '<td width="',$width,'">', getInfo( $idHeader, $fr ), '</td>';
            }
         ?>
    </tr>
<?php endforeach; ?>

</table>

<?php

novafrs_footer ();
?>
