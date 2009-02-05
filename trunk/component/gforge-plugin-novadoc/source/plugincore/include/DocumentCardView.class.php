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

require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/DocumentGroupHTML.class.php");

class DocumentCardView{
    var $config;
    var $canEdit;
    
    function DocumentCardView($canEdit, $group_id){
        $this->config = DocumentConfig::getInstance();
        $this->canEdit = $canEdit;
        $this->group_id = $group_id;
    }
    
    function printName( $text, $required=false ){
        ?>
        <strong class="libelle"> 
            <?= $text ?> 
            <?php if( $required ) echo utils_requiredField(); ?>
        </strong> 
        <?php
    }
    
    function printField( $text, $name, $value, $required, $size=40, $maxlength=250, $colspan=1 ){
        ?>
            <td <?php if( $colspan>1 ) echo 'colspan="', $colspan, '"' ?> >
                <?php $this->printName( $text, $required ); ?>
        		<input type="text" name="<?=$name?>" size="<?=$size?>" maxlength="<?=$maxlength?>" value="<?=$value?>" 
        		    <?php if(!$this->canEdit) echo ' disabled="disabled" '; ?>
        		 />
            </td>
        <?php
    }
    
	function printHistories ($histories)
	{
		global
			$Language,
			$sys_datefmt;

        	if ($histories == null)
		{
			return;
		}
?>
<script language="JavaScript" type="text/JavaScript">
	/* Affiche ou masque l'historique */
	function affMasqueHistories( ){
		var elt = document.getElementById( 'histories'  );
		if( elt.style.display=='none' || elt.style.display=='' ){
			elt.style.display='block';
			var elt = document.getElementById( 'displayHistories'  );
			elt.style.display='none';
		}else{
			elt.style.display='none';
		}
	}         
</script>
<a id="displayHistories" href="#" onclick="affMasqueHistories(); return false;"> 
<? echo dgettext ("gforge-plugin-novadoc", "displayHistory"); ?>
<br /><br /> 
</a>
<div id="histories" style="display:none;"><table class="cardTable tcenter">
	<tr>
		<th> <?php echo dgettext('gforge-plugin-novadoc','date') ?> </th>
		<th> <?php echo dgettext('gforge-plugin-novadoc','version') ?> </th>
		<th> <?php echo dgettext('gforge-plugin-novadoc','status') ?> </th>
		<th> <?php echo dgettext('gforge-plugin-novadoc','title') ?> </th>
	</tr>             
<?
        	foreach ($histories as $doc)
		{
			if ($doc->isURL ())
			{
				$linkDownload = $doc->getFileName ();
			}
			else
			{
				$linkDownload = "view.php/" . $doc->Group->getID () . "/" . $doc->getID () . "/" . urlencode (novadoc_unixString ($doc->getFileName ())) . "?history=1";
			}
			$linkFiche = 'card.php?group_id='. $this->group_id .'&docid=' . $doc->getID();
			$datestamp = $doc->getUpdateDate();
			if ($datestamp == 0)
			{
				$datestamp = $doc->getCreateDate();
			}
			$date_string = date ($sys_datefmt, $datestamp);
			$status = isset ($this->config->statusText [$doc->getStatus ()]) ? $this->config->statusText [$doc->getStatus ()] : "";
?>
	<tr>
		<td width="150px"><? echo $date_string; ?></td>
		<td width="250px"><? echo $doc->getVersion(); ?></td>
		<td width="250px"><? echo $status; ?></td>
		<td><a href="<? echo $linkDownload; ?>"><? echo $doc->getName (); ?></a> <a href="<? echo $linkFiche; ?>"> (<? echo dgettext ("gforge-plugin-novadoc", "viewCard"); ?>)</a></td>
	</tr>
<?
		}
?>
</table></div>
<?
	}

    /**
     * @param $group_id id du groupe (projet)
     * @param $Language 
     * @param $doc class Document : le doc a afficher
     * @param $g l'objet group
     * @param $dgf class DocumentGroupFactory
     * @param $
     */
    function printCard( $group_id, &$Language, &$doc, &$g, &$dgf, $histories ){
        $editDoc = $doc->getId() > 0;
    ?>
        <style type="text/css">
            .libelle{
                width: 140px;
                float: left;
            }
            .cardTable{
                width: 950px;
                border : solid 1px black;
                margin-bottom: 10px;
                padding: 10px 5px 10px 10px;
            }
            .cardTable td{
                vertical-align: middle;
                height: 30px;
            }
            .cardTable th{
                background-color: #d0d4e1;
            }
            .tcenter tr{
                text-align: center;
                border: solid 1px black;
            }
            .tabcol1{
                width: 130px;
                text-align: left;
            }
        </style>

       	<form name="adddata" action="<?php echo $_SERVER['PHP_SELF'],"?group_id=$group_id"; ?>" method="post" enctype="multipart/form-data">
       	<?php if( $editDoc ) : ?>
       	    <input type="hidden" name="docid" value="<?= $doc->getID() ?>" />
       	<?php endif; ?>
       	
        <table class="cardTable" >
           	<?php if( $editDoc ) : ?>
            	<tr>
            		<td colspan="2">
            		<strong class="libelle"><?php echo dgettext('gforge-plugin-novadoc','file')?></strong>
            		<?php if ($doc->isURL()) {
            			echo '<a href="',$doc->getFileName(),'">[',dgettext('gforge-plugin-novadoc','viewURL'),']</a>';
            		} else { ?>
            		    <a target="_blank" href="view.php/<?php echo $group_id.'/'.$doc->getID().'/'.urlencode(novadoc_unixString ($doc->getFileName())) ?>"><?php echo htmlspecialchars( $doc->getFileName() ); ?></a>
            		<?php } ?>
            		</td>
            	</tr>
            <?php endif; ?>        
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novadoc','title'), 'title', $doc->getName(), true );
                    $this->printField( dgettext('gforge-plugin-novadoc','author'), 'author', $doc->getAuthor(), false );
                ?>                    
            </tr>
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novadoc','description'), 'description', $doc->getDescription(), false, 119, 250, 2 );
                ?>
            </tr>
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novadoc','writingDate'), 'writingDate', $doc->getWritingDate(), false, 12 );
                    $this->printField( dgettext('gforge-plugin-novadoc','doctype'), 'doctype', $doc->getDocType(), false, 30 );
                ?>                    
            </tr>
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novadoc','version'), 'version', $doc->getVersion(), false, 15 );
                    $this->printField( dgettext('gforge-plugin-novadoc','reference'), 'reference', $doc->getReference(), false, 30 );
                ?>                    
            </tr>
            <tr>
                <td>
                    <?php
                        $this->printName( dgettext('gforge-plugin-novadoc','language') );
            	        echo html_get_language_popup($Language,'language_id',$doc->getLanguageID() ); 
            	    ?>
            	</td>
            </tr>
            <tr>
                <td>
        		<?php
			$this->printName( dgettext('gforge-plugin-novadoc','status') );
			echo novadoc_select_box_status ($this->config->statusText, $doc->getStatus ());
        		?>
        		</td>
        		<?php if( $editDoc ): ?>
            		<td>
            		    <?php if( $this->config->useState ): ?>
            		        <strong><?php echo dgettext('gforge-plugin-novadoc','state') ?>:</strong>
            		        <?php novadoc_get_state_box ($doc->getStateID ()); ?>
            		    <?php endif; ?>
            		    <?php if($this->canEdit and !$doc->isDeleted() ): ?>
            		        <a href="?group_id=<?=$group_id?>&amp;docid=<?=$doc->getId()?>&amp;delete_doc=1" style="margin-left:30px"> 
                		        <?php echo dgettext('gforge-plugin-novadoc','delete_doc') ?>
            		        </a>
            		    <?php endif; ?>
            		</td>
                <?php else: ?>
                    <td>&nbsp;</td>
                <?php endif; ?>
            </tr>
            <tr>
        		<?php
        		    $this->printField( dgettext('gforge-plugin-novadoc','observation'), 'observation', $doc->getObservation(), false, 119, 250,2 );
        		?>
            </tr>
        </table>
        
        <?php if( $this->config->statusTable ): ?>
            <table class="cardTable tcenter" >
                 <tr>
                    <th class="tabcol1"> &nbsp; </th>
                    <th> <?php echo dgettext('gforge-plugin-novadoc','date') ?> </th>
                    <th> <?php echo dgettext('gforge-plugin-novadoc','name') ?> </th>
                    <th> <?php echo dgettext('gforge-plugin-novadoc','description') ?> </th>
                 </tr>
                 <?php foreach( $this->config->statusTable as $k=>$lib ): ?>
                    <tr>
                        <th class="tabcol1"> <?= $lib ?> </th>
                        <td> 
                            <input type="text" name="statusDate<?=$k?>" size="10" maxlength="150" value="<?=$doc->tableStatus[$k]['date']; ?>" <?php if(!$this->canEdit) echo ' disabled="disabled" '; ?> />
                        </td>
                        <td> 
                            <input type="text" name="statusName<?=$k?>" size="20" maxlength="150" value="<?=$doc->tableStatus[$k]['name']; ?>" <?php if(!$this->canEdit) echo ' disabled="disabled" '; ?> />
                        </td>
                        <td> 
                            <input type="text" name="statusDesc<?=$k?>" size="80" maxlength="150" value="<?=$doc->tableStatus[$k]['description']; ?>" <?php if(!$this->canEdit) echo ' disabled="disabled" '; ?> />
                        </td>
                    </tr>
                 <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        <?php if($this->canEdit): ?>
            <table class="cardTable">
            <tr >
                <td>
                    <strong>
                	    <?php echo dgettext('gforge-plugin-novadoc','group'); ?>
                    </strong>
                 </td>
                 <td>
                    <?php
                		$dgh = new DocumentGroupHTML($g);
                		if ($dgh->isError()) {
                			exit_error('Error',$dgh->getErrorMessage());
                		}
                
                		if( $editDoc ){
                		    $selected_group = $doc->getDocGroupID();    
                		}else if( isset( $_GET['selected_doc_group'] ) ){
                		    $selected_group = $_GET['selected_doc_group'];
                		}else{
                		    $selected_group = 0;
                		}
                		$dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $selected_group );
                		
                	?>
                </td>
             </tr>
             
             
             <?php if( $editDoc ) : ?>
            	<tr>
            		<?php if ($doc->isURL()) { ?>
                		<td>
                		    <strong><?php echo dgettext('gforge-plugin-novadoc','editdocs_upload_url') ?> </strong><?php echo utils_requiredField(); ?>
                		</td>
                		<td>
                            <input type="text" name="file_url" size="50" value="<?php echo $doc->getFileName() ?>" />
                        </td>
            		<?php } else { ?>
            		    <td>
                		    <strong><?php echo dgettext('gforge-plugin-novadoc','upload') ?></strong>
                		</td>
                		<td style="height:50px;">
            		        <input type="file" name="uploaded_data" size="30" />
    
                                <br /><input type="checkbox" name="archive" value="1" />
                                <strong>
                                    <?php echo dgettext('gforge-plugin-novadoc','archive'); ?>
                                 </strong>
            		        
            			    <?php if ( isset($sys_use_ftpuploads) and $sys_use_ftpuploads) { ?>
            			        <strong><?php echo sprintf( dgettext ( 'gforge-plugin-novadoc' , 'upload_ftp' ) ,array($sys_ftp_upload_host)) ?></strong>
                    			<?php
                    			$arr[]='';
                    			$ftp_files_arr=array_merge($arr,ls($upload_dir,true));
                    			echo html_build_select_box_from_arrays($ftp_files_arr,$ftp_files_arr,'ftp_filename','');
                			}
                		}
                		?>
             		    </td>
            	</tr>
            <?php else: ?>         
                 <tr>
                    <td>
                        <strong>	<?php echo dgettext('gforge-plugin-novadoc','upload_file') ?> </strong> <?php echo utils_requiredField(); ?>
                     </td>
                     <td style="height:50px;">
        		        <input type="file" name="uploaded_data" size="50" /><br />
        		    </td>
        		</tr>
        		<tr>
        		    <td>
        		        <strong>	<?php echo dgettext('gforge-plugin-novadoc','upload_url') ?> </strong> <?php echo utils_requiredField(); ?>
        		    </td>
        		    <td>
        		        <input type="text" name="file_url" size="80" />
        		    </td>
        		</tr>
    		<?php endif; ?>
    		</table>
        <?php endif; /*canEdit*/?>
		
		<?php 
		    $this->printHistories( $histories );
		?>


        <?php if( $this->canEdit ): ?>
    		<input type="submit" name="submit" value="	<?php echo dgettext('gforge-plugin-novadoc','submit') ?> " />
        <?php endif; ?>


		
        <?php if( !$this->canEdit ): ?>
            <script language="JavaScript" type="text/JavaScript">
                var tabInputSelect = Array( 'language_id', 'status', 'doctype' );
                for( i=0; i< tabInputSelect.length; i++ ){
                    document.getElementsByName( tabInputSelect[i] )[0].disabled = 'disabled';
                }
            </script> 
        <?php endif; ?>
		
	    </form>
    <?php
    }

    function printConfirm( $group_id, $docid, &$Language ){
        ?>
        <p>
		    <form action="<?php echo $_SERVER['PHP_SELF'].'?deletedoc=1&amp;docid='.$docid.'&amp;group_id='.$group_id ?>" method="post">
    		    <?php echo dgettext('gforge-plugin-novadoc','delete_warning'); ?>
    		    <p>
    		        <input type="checkbox" name="confirm_delete" value="1"><?php echo dgettext('gforge-plugin-novadoc','sure') ?><br />
        		<p>
    	    	<input type="submit" name="post_changes" value="<?php echo dgettext('gforge-plugin-novadoc','delete') ?>" /></p>
		    </form>
		</p>
		<?php

    }
    
}

?>
