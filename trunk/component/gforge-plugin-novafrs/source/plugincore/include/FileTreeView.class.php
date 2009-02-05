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

require_once ("plugins/novafrs/include/FileConfig.class.php");

class FileTreeView
{
    var $config;
    var $canEditStatus;
    var $auth;  // a FileGroupAuth object
    
    function FileTreeView( $canEditStatus, $auth ){
        $this->config = FileConfig::getInstance();        
        $this->canEditStatus = $canEditStatus;
        $this->auth = $auth;
    }
    
    
    function print_css(){
    ?>
        <style type="text/css">
            #files table{
                width : 100%;
            }
            
             #files table td, #files table th{
                  font-size : 10pt;
                  text-align: left;
             }
            .frIcon{
                height: 14px;
                margin-right: 4px;
            }
            
            .zoneFr{
                display: none;  /* Les zones sont masquées */
            }
            
            a.frRep{
                font-weight: bold;
                color: black;
            }
            
            a.frFic{
                color: black;
            }
            
            a img{
                border: 0px;
            }
        
        </style>
    <?php
    }
    
    function print_js(){
    ?>

        <script language="JavaScript" type="text/JavaScript">
        
        /* Affiche ou masque un repertoire */
        function affMasqueRep( noId ){
            var elt = document.getElementById( '<?= $this->config->idHtmlRep ?>' + noId  );
            var img = document.getElementById( '<?= $this->config->idImgRep ?>' + noId );
            if( !elt || !img ) return;
            if( elt.style.display=='none' || elt.style.display=='' ){
                elt.style.display='block';
                img.src = '<?= $this->config->imgRepO ?>';
            }else{
                elt.style.display='none';
                img.src = '<?= $this->config->imgRepF ?>';
            }
        }
        
        /* Développe tout */
        function devTout( debut, fin ){
            for( i=debut; i<=fin; i++ ){
                var elt = document.getElementById( '<?= $this->config->idHtmlRep ?>' + i );
                var img = document.getElementById( '<?= $this->config->idImgRep ?>'+i );
                elt.style.display='block';
                img.src = '<?= $this->config->imgRepO ?>';
            }
        }
        
        /* Déplie tout */
        function deplieTout( debut, fin ){
            for( i=debut; i<=fin; i++ ){
                var elt = document.getElementById( '<?= $this->config->idHtmlRep ?>' + i );
                var img = document.getElementById( '<?= $this->config->idImgRep ?>'+i );
                elt.style.display='none';
                img.src = '<?= $this->config->imgRepF ?>';
            }
        }
        
        
        /* Changement de statut envoie du formulaire */
        function chgStatut( idFr ){
            // Constuction d'une chaine contenant les dossiers ouverts
            var dossiersOuverts = '';
            var i=1;
            while( true ){
                var dossier = document.getElementById( '<?= $this->config->idHtmlRep ?>' + i );
                if( !dossier ) break;
                if( dossier.style.display=='block' ){
                    dossiersOuverts += ( i + ';' );
                }
                i++;
            }
            document.getElementById( 'inp_frId' ).value = idFr;
            document.getElementById( 'inp_statusId' ).value = document.getElementById( 'selStat'+idFr ).value;
            document.getElementById( 'inp_dossiersOuverts' ).value = dossiersOuverts;
            document.getElementById( 'inp_scrollLeft' ).value = document.body.scrollLeft;
            document.getElementById( 'inp_scrollTop' ).value = document.body.scrollTop;
            document.getElementById( 'formChgStatut' ).submit();
        }
    
        </script>
    <?php
    }
    
    
    function print_js_redraw( $tabDossiersOuverts, $scrollLeft, $scrollTop ){
        ?>
        <script language="JavaScript" type="text/JavaScript">
            <?php foreach( $tabDossiersOuverts as $id ): ?>
                affMasqueRep( <?= $id ?> );
            <?php endforeach; ?>
            function setScrool(){
                document.body.scrollLeft = <?= $scrollLeft ?>;
                document.body.scrollTop = <?= $scrollTop ?>;
            }
            window.onload = setScrool;
        </script>
        <?php        
    }
    
    function print_table_header( ){
        global $Language;
    ?>
        <form action="" method="POST" id="formChgStatut">
            <input type="hidden" id="inp_frId" name="frId" value="" />
            <input type="hidden" id="inp_statusId" name="statusId" value="" />
            <input type="hidden" id="inp_dossiersOuverts" name="dossiersOuverts" value="" />
            <input type="hidden" id="inp_scrollLeft" name="scrollLeft" value="" />
            <input type="hidden" id="inp_scrollTop" name="scrollTop" value="" />
        </form>
        <table>
        <tr>
            <th> <?= dgettext('gforge-plugin-novafrs','head_file')?> </th>
            <th width="<?=$this->config->tailleType?>px"> <?= dgettext('gforge-plugin-novafrs','head_type')?> </th>
            <th width="<?=$this->config->tailleTaille?>px"> <?= dgettext('gforge-plugin-novafrs','head_size')?> </th>
            <th width="<?=$this->config->tailleStatut?>px"> <?= dgettext('gforge-plugin-novafrs','status')?> </th>
            <th width="<?=$this->config->tailleStatutModif?>px"> <?= dgettext('gforge-plugin-novafrs','head_modif_by')?> </th>
            <th width="<?=$this->config->tailleStatutDate?>px"> <?= dgettext('gforge-plugin-novafrs','head_the')?> </th>
        </tr>
        </table>
    <?php
    }
    
    
    
    /**
     * Retourne un numéro unique 
     * @param $increment true si le no doit être incrémenté
     */
    function newNoGroup( $increment = true ){
        static $noGroup = 0;
        if( $increment ) $noGroup++;
        return $noGroup;
    }
    
    /**
     * Retourne le code html de l'affichage du choix d'un statut
     * @param $idSelect id du statut selectionné
     */
	function getHtmlStatut (&$fr)
	{
		$id = $fr->getID ();
		if (($this->canEditStatus) && ($this->auth->canWrite ($fr->getFrGroupID ())))
		{
			$html = novafrs_select_box_status ($this->config->statusText, $fr->getStatus (), "selStat" . $id) . '<input type="button" value="Ok" onclick="chgStatut('. $id . ');" />';
		}
		else
		{
			$html = isset ($this->config->statusText [$fr->getStatus ()] )? $this->config->statusText [$fr->getStatus ()] : "";
		}
		return $html;           
	}


   function getTaille( &$fr ){
        $t = $fr->getFileSize();
        if( $t < 1024 ){
            return $t. ' o';
        }
        $t = round( $t / 1024, 0 );
        if( $t < 1024 ){
            return $t.' Ko'; 
        }
        $t = round( $t / 1024, 1 );
        return $t.' Mo'; 
    }

    
    /**
     * Retourne le code html dédié à l'affichage d'un file
     * @param $fr le file (class File)
     * @param $depth profondeur du fruement dans l'arbre
     * @return code html dédié à l'affichage d'un file
     */
    function get_html_fr( &$fr, $depth ){         
        global $Language;
        $link = (( $fr->isURL() ) ? $fr->getFileName() : "view.php/".$fr->Group->getID()."/".$fr->getID()."/". urlencode(novafrs_unixString($fr->getFileName())) );
        $link_edit = '/plugins/novafrs/card.php?group_id='.$fr->Group->getID() . '&frid=' . $fr->getID();
        return '<tr ' . $GLOBALS['HTML']->boxGetAltRowStyle(0) . ' >'.
                '<td style="padding-left:'.$depth*$this->config->decalage.'px;">'. 
                        '<img src="'. $this->config->imgFr. '" class="frIcon" />'.
                        //<a href='".($from_admin ? "../" : "")."new.php?group_id=".$fr_group->Group->getID()."&amp;selected_fr_group=".$fr_group->getID()."'>";
                        //'<a href="new.php?group_id='.$fr->Group->getID().'&amp;selected_fr_group='.$fr->getID().'" class="frFic">'. $fr->getName() . '</a>'.
                        '<a href="'.$link.'">'. $fr->getName() . '</a>'.
                        ' <a href="'.$link_edit.'">('.dgettext('gforge-plugin-novafrs','viewCard').')</a>' .
                '</td>'.
                '<td width="'. $this->config->tailleType. 'px">'.
                     $this->config->typeText[$fr->getFrType()] .
                '</td>'.
                '<td width="'. $this->config->tailleTaille. 'px">'.
                     $this->getTaille( $fr ) .
                '</td>'.
                '<td width="'. $this->config->tailleStatut. 'px">'.
                     $this->getHtmlStatut( $fr ) .
                '</td>'.
                '<td width="'. $this->config->tailleStatutModif. 'px">'. 
                    $fr->getStatusModifBy() . 
                '</td>'.
                '<td width="'. $this->config->tailleStatutDate. 'px">'. 
                    date('d/m/Y',$fr->getStatusModifDate()) . 
                '</td>'.
             '</tr>'."\n\n";        
    }
    

    /**
     * Retourne le code html dédié  à l'affichage d'un répertoire
     * @param $rep répertoire (class Rep2Leaf)
     * @param $noZone le numéro de zone du répertoire (permettant de masquer ses fils)
     * @param $depth profondeur du répertoire par rapport à la racine : affichage d'un décalage horizontal
     * @param $noZoneFin numéro de zone jusqu'à laquelle il faut développer lors d'un clique sur développer tout
     * @return code html dédié  à l'affichage d'un répertoire
     */
    function get_html_group( &$group, $noZone, $depth, $noZoneFin ){
        return '<table>'.
                ' <tr '. $GLOBALS['HTML']->boxGetAltRowStyle(0). ' >'.
                    '<td style="padding-left:'.$depth*$this->config->decalage.'px;"> '.
                        '<a href="#"  class="frRep" onClick="affMasqueRep(\''. $noZone. '\');return false;" >'.
                            '<img id="'. $this->config->idImgRep . $noZone .'" src="'. $this->config->imgRepF. '" class="frIcon"  />'.
                            $group->getName() . 
                        '</a>'.
                        '<a href="#"  class="frRep" onClick="devTout('. $noZone. ', ' . $noZoneFin . ');return false;" >'.
                            ' +'.
                        '</a>'.
                        '<a href="#"  class="frRep" onClick="deplieTout('. $noZone. ', ' . $noZoneFin . ');return false;" >'.
                            ' -'.
                        '</a>'.
                    '</td>'.
                ' </tr> '.
              '</table>'. "\n";
    }



  

    function get_html_arborescence( $idFrGroup, &$nestedGroups, &$nestedFrs, &$file_factory, $depth) {
        $html = '';
    	global $group_id;

        // foreach branch    
        if( isset( $nestedGroups[$idFrGroup] )  ){
        	foreach ($nestedGroups[$idFrGroup] as $dg) {
        	    //function hasFiles(&$nested_groups, &$file_factory, $stateid=0) {
        	    if( $this->config->displayEmptyGroup || $dg->hasFiles( $nestedGroups, $file_factory ) ){
                    $noZone = $this->newNoGroup();
                    
                    $htmlAdd  = '<div id="' . $this->config->idHtmlRep . $noZone . '" class="zoneFr" >';
        
          		    $htmlAdd .= $this->get_html_arborescence( $dg->getID(), $nestedGroups, $nestedFrs, $file_factory, $depth+1 );
        
            		$noZoneFin = $this->newNoGroup(false);
                    $htmlAdd =  $this->get_html_group( $dg, $noZone, $depth, $noZoneFin ) . $htmlAdd . '</div>';
                    
                    $html .= $htmlAdd;
                }
        	}
        }
        // foreach file in this branch
        if( isset( $nestedFrs[$idFrGroup] ) ){
            $html .= '<table>';
    		foreach ($nestedFrs[$idFrGroup] as $fr) {
    			$html .= $this->get_html_fr( $fr, $depth );
	    	}
            $html .=  '</table>';
        }
    	
    	return $html;
    }
    
    function print_tree(  &$nestedGroups, &$nestedFrs, &$file_factory ){
        $this->print_css();
        $this->print_js();
        echo '<div id="files">';
        $this->print_table_header();
        echo $this->get_html_arborescence( 0, $nestedGroups, $nestedFrs, $file_factory, 0);
        echo '</div>';
    }


    function devToutArborescence(){        
        ?>
        <script language="JavaScript" type="text/JavaScript">
            devTout( 1, <?= $this->newNoGroup( false ) ?> );
        </script>
        <?php
        
    }
    
}

?>
