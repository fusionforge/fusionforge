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

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novadoc/include/Document.class.php");
require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/DocumentGroupAuth.class.php");
require_once ("plugins/novadoc/include/utils.php");

if (!$group_id) {
    exit_no_group();
}
$g =& group_get_object ($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$config = DocumentConfig::getInstance();

novadoc_header (dgettext ('gforge-plugin-novadoc','title_display'));

$d = new Document($g);
$chronos = $d->getChronoTable();


function getInfo( $idInfo, &$doc ){
    global $sys_datefmt, $Language;
    
    switch( $idInfo ){
        case "chrono" :
            $ret = $doc->getChrono();
            break;
        case "title" :
            $link = (( $doc->isURL() ) ? $doc->getFileName() : "view.php/".$doc->Group->getID()."/".$doc->getID()."/". urlencode(novadoc_unixString($doc->getFileName())) );
            $link_edit = '/plugins/novadoc/card.php?group_id='.$doc->Group->getID() . '&docid=' . $doc->getID();
            
            $ret =  '<a href="'.$link.'">'. $doc->getName() . '</a>'.
                    ' <a href="'.$link_edit.'">('.dgettext('gforge-plugin-novadoc','viewCard').')</a>';

            break;
        case "author" :
            $ret = $doc->getAuthor();
            break;
        case "description" :
            $ret = $doc->getDescription();
            break;
        case "writingDate" :
            // $ret = date($sys_datefmt, $doc->getCreateDate() );
            $ret = $doc->getWritingDate();
            break;
        case "updatedate" :
            $datestamp = $doc->getUpdateDate();
            if( $datestamp == 0 ) $datestamp = $doc->getCreateDate();
            $date_string = date($sys_datefmt, $datestamp );
            $ret = $date_string;
            break;
        case "version" :
            $ret = $doc->getVersion();
            break;
        case "type" :
            $ret = $doc->getDocType();
            break;
        case "reference" :
            $ret = $doc->getReference();
            break;
        case "status" :
            if( $doc->isDeleted() ){
                $ret = dgettext('gforge-plugin-novadoc','docDeleted');
            }else{
                $config = DocumentConfig::getInstance(); 
                $ret = isset( $config->statusText[$doc->getStatus()] )? $config->statusText[$doc->getStatus()] : '?';
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
            return dgettext('gforge-plugin-novadoc','chrono');
            break;
        case "title" :
            return dgettext('gforge-plugin-novadoc','title');
            break;
        case "author" :
            return dgettext('gforge-plugin-novadoc','author');
            break;
        case "description" :
            return dgettext('gforge-plugin-novadoc','description');
            break;
        case "writingDate" :
            return dgettext('gforge-plugin-novadoc','writingDate');
            break;
        case "updatedate" :
            return dgettext('gforge-plugin-novadoc','updateDate');
            break;
        case "version" :
            return dgettext('gforge-plugin-novadoc','version');
            break;
        case "type" :
            return dgettext('gforge-plugin-novadoc','doctype');
            break;
        case "reference" :
            return dgettext('gforge-plugin-novadoc','reference');
            break;
        case "status" :
            return dgettext('gforge-plugin-novadoc','status');
            break;
        default : return "bad id: $idInfo ";
    }
}

$chronoTable = $config->chronoTable;

$auth = new DocumentGroupAuth( $group_id, $LUSER );


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
<?php foreach( $chronos as $doc ): ?>
    <?php 
        if( !$auth->canRead( $doc->getDocGroupID() ) ) continue;
    ?>
    <tr>
        <?php
            foreach( $chronoTable as $idHeader => $width  ){
                echo '<td width="',$width,'">', getInfo( $idHeader, $doc ), '</td>';
            }
         ?>
    </tr>
<?php endforeach; ?>

</table>

<?php

novadoc_footer ();
?>
