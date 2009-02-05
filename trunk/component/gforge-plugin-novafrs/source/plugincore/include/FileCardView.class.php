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
require_once ("plugins/novafrs/include/FileGroupHTML.class.php");

class FileCardView
{
    var $config;
    var $canEdit;
    
    function FileCardView($canEdit, $group_id){
        $this->config = FileConfig::getInstance();
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
<? echo dgettext ("gforge-plugin-novafrs", "displayHistory"); ?>
<br /><br /> 
</a>
<div id="histories" style="display:none;"><table class="cardTable tcenter">
	<tr>
		<th> <?php echo dgettext('gforge-plugin-novafrs','date') ?> </th>
		<th> <?php echo dgettext('gforge-plugin-novafrs','version') ?> </th>
		<th> <?php echo dgettext('gforge-plugin-novafrs','status') ?> </th>
		<th> <?php echo dgettext('gforge-plugin-novafrs','title') ?> </th>
	</tr>             
<?        
		foreach ($histories as $fr)
		{
			if ($fr->isURL ())
			{
				$linkDownload = $fr->getFileName ();
			}
			else
			{
				$linkDownload = "view.php/" . $fr->Group->getID () . "/" . $fr->getID () . "/" . urlencode (novafrs_unixString ($fr->getFileName ())) . "?history=1";
			}
			$linkFiche = 'card.php?group_id='. $this->group_id .'&frid=' . $fr->getID();
			$datestamp = $fr->getUpdateDate();
			if ($datestamp == 0)
			{
				$datestamp = $fr->getCreateDate ();
			}
			$date_string = date($sys_datefmt, $datestamp );
			$status = isset( $this->config->statusText[$fr->getStatus()] )? $this->config->statusText[$fr->getStatus()] : '';
?>
	<tr>
		<td width="150px"><? echo $date_string; ?></td>
		<td width="250px"><? echo $fr->getVersion (); ?></td>
		<td width="250px"><? echo $status; ?></td>
		<td><a href="<? echo $linkDownload; ?> "><? echo $fr->getName (); ?></a> <a href="<? echo $linkFiche; ?>"> (<? echo dgettext ("gforge-plugin-novafrs", "viewCard"); ?>)</a></td>
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
     * @param $fr class File : le fr a afficher
     * @param $g l'objet group
     * @param $dgf class FileGroupFactory
     * @param $
     */
    function printCard( $group_id, &$Language, &$fr, &$g, &$dgf, $histories ){
        $editFr = $fr->getId() > 0;
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
       	<?php if( $editFr ) : ?>
       	    <input type="hidden" name="frid" value="<?= $fr->getID() ?>" />
       	<?php endif; ?>
       	
        <table class="cardTable" >
           	<?php if( $editFr ) : ?>
            	<tr>
            		<td colspan="2">
            		<strong class="libelle"><?php echo dgettext('gforge-plugin-novafrs','file')?></strong>
            		<?php if ($fr->isURL()) {
            			echo '<a href="',$fr->getFileName(),'">[',dgettext('gforge-plugin-novafrs','viewURL'),']</a>';
            		} else { ?>
            		    <a target="_blank" href="view.php/<?php echo $group_id.'/'.$fr->getID().'/'.urlencode(novafrs_unixString($fr->getFileName())) ?>"><?php echo htmlspecialchars( $fr->getFileName() ); ?></a>
            		<?php } ?>
            		</td>
            	</tr>
            <?php endif; ?>        
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novafrs','title'), 'title', $fr->getName(), true );
                    $this->printField( dgettext('gforge-plugin-novafrs','author'), 'author', $fr->getAuthor(), false );
                ?>                    
            </tr>
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novafrs','description'), 'description', $fr->getDescription(), false, 119, 250, 2 );
                ?>
            </tr>
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novafrs','writingDate'), 'writingDate', $fr->getWritingDate(), false, 12 );
                ?>                    
                <td>
                    <?php 
                        $this->printName( dgettext('gforge-plugin-novafrs','frtype'), false ); 
                        echo novafrs_select_box_type ($this->config->typeText, $editFr ? $fr->getFrType () : $this->config->typeDefault);
                    ?>
                </td>                    
            </tr>
            <tr>
                <?php 
                    $this->printField( dgettext('gforge-plugin-novafrs','version'), 'version', $fr->getVersion(), false, 15 );
                    $this->printField( dgettext('gforge-plugin-novafrs','reference'), 'reference', $fr->getReference(), false, 30 );
                ?>                    
            </tr>
            <tr>
                <td>
                    <?php
                        $this->printName( dgettext('gforge-plugin-novafrs','language') );
            	        echo html_get_language_popup($Language,'language_id',$fr->getLanguageID() ); 
            	    ?>
            	</td>
            </tr>
            <tr>
                <td>
        		<?php
			$this->printName (dgettext ('gforge-plugin-novafrs', 'status'));
			echo novafrs_select_box_status ($this->config->statusText, $fr->getStatus ());
        		?>
        		</td>
        		<?php if( $editFr ): ?>
            		<td>
            		    <?php if( $this->config->useState ): ?>
            		        <strong><?php echo dgettext('gforge-plugin-novafrs','state') ?>:</strong>
            		        <?php novafrs_get_state_box ($fr->getStateID ()); ?>
            		    <?php endif; ?>
            		    <?php if($this->canEdit and !$fr->isDeleted() ): ?>
            		        <a href="?group_id=<?=$group_id?>&amp;frid=<?=$fr->getId()?>&amp;delete_fr=1" style="margin-left:30px"> 
                		        <?php echo dgettext('gforge-plugin-novafrs','delete_fr') ?>
            		        </a>
            		    <?php endif; ?>
            		</td>
                <?php else: ?>
                    <td>&nbsp;</td>
                <?php endif; ?>
            </tr>
            <tr>
        		<?php
        		    $this->printField( dgettext('gforge-plugin-novafrs','observation'), 'observation', $fr->getObservation(), false, 119, 250,2 );
        		?>
            </tr>
        </table>
        
        <?php if( $this->config->statusTable ): ?>
            <table class="cardTable tcenter" >
                 <tr>
                    <th class="tabcol1"> &nbsp; </th>
                    <th> <?php echo dgettext('gforge-plugin-novafrs','date') ?> </th>
                    <th> <?php echo dgettext('gforge-plugin-novafrs','name') ?> </th>
                    <th> <?php echo dgettext('gforge-plugin-novafrs','description') ?> </th>
                 </tr>
                 <?php foreach( $this->config->statusTable as $k=>$lib ): ?>
                    <tr>
                        <th class="tabcol1"> <?= $lib ?> </th>
                        <td> 
                            <input type="text" name="statusDate<?=$k?>" size="10" maxlength="150" value="<?=$fr->tableStatus[$k]['date']; ?>" <?php if(!$this->canEdit) echo ' disabled="disabled" '; ?> />
                        </td>
                        <td> 
                            <input type="text" name="statusName<?=$k?>" size="20" maxlength="150" value="<?=$fr->tableStatus[$k]['name']; ?>" <?php if(!$this->canEdit) echo ' disabled="disabled" '; ?> />
                        </td>
                        <td> 
                            <input type="text" name="statusDesc<?=$k?>" size="80" maxlength="150" value="<?=$fr->tableStatus[$k]['description']; ?>" <?php if(!$this->canEdit) echo ' disabled="disabled" '; ?> />
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
                	    <?php echo dgettext('gforge-plugin-novafrs','group'); ?>
                    </strong>
                 </td>
                 <td>
                    <?php
                		$dgh = new FileGroupHTML($g);
                		if ($dgh->isError()) {
                			exit_error('Error',$dgh->getErrorMessage());
                		}
                
                		if( $editFr ){
                		    $selected_group = $fr->getFrGroupID();    
                		}else if( isset( $_GET['selected_fr_group'] ) ){
                		    $selected_group = $_GET['selected_fr_group'];
                		}else{
                		    $selected_group = 0;
                		}
                		$dgh->showSelectNestedGroups($dgf->getNested(), 'fr_group', false, $selected_group );
                		
                	?>
                </td>
             </tr>
             
             
             <?php if( $editFr ) : ?>
            	<tr>
            		<?php if ($fr->isURL()) { ?>
                		<td>
                		    <strong><?php echo dgettext('gforge-plugin-novafrs','upload_url') ?> </strong><?php echo utils_requiredField(); ?>
                		</td>
                		<td>
                            <input type="text" name="file_url" size="50" value="<?php echo $fr->getFileName() ?>" />
                        </td>
            		<?php } else { ?>
            		    <td>
                		    <strong><?php echo dgettext('gforge-plugin-novafrs','upload') ?></strong>
                		</td>
                		<td style="height:50px;">
            		        <input type="file" name="uploaded_data" size="30" />
    
                                <br /><input type="checkbox" name="archive" value="1" />
                                <strong>
                                    <?php echo dgettext('gforge-plugin-novafrs','archive'); ?>
                                 </strong>
            		        
            			    <?php if ( isset($sys_use_ftpuploads) and $sys_use_ftpuploads) { ?>
            			        <strong><?php echo sprintf( dgettext ( 'gforge-plugin-novafrs' , 'upload_ftp' ) ,array($sys_ftp_upload_host)) ?></strong>
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
                        <strong>	<?php echo dgettext('gforge-plugin-novafrs','upload_file') ?> </strong> <?php echo utils_requiredField(); ?>
                     </td>
                     <td style="height:50px;">
        		        <input type="file" name="uploaded_data" size="50" /><br />
        		    </td>
        		</tr>
        		<tr>
        		    <td>
        		        <strong>	<?php echo dgettext('gforge-plugin-novafrs','or_upload_url') ?> </strong> <?php echo utils_requiredField(); ?>
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
    		<input type="submit" name="submit" value="	<?php echo dgettext('gforge-plugin-novafrs','submit') ?> " />
        <?php endif; ?>


		
        <?php if( !$this->canEdit ): ?>
            <script language="JavaScript" type="text/JavaScript">
                var tabInputSelect = Array( 'language_id', 'status', 'frtype' );
                for( i=0; i< tabInputSelect.length; i++ ){
                    document.getElementsByName( tabInputSelect[i] )[0].disabled = 'disabled';
                }
            </script> 
        <?php endif; ?>
		
	    </form>
    <?php
    }

    function printConfirm( $group_id, $frid, &$Language ){
        ?>
        <p>
		    <form action="<?php echo $_SERVER['PHP_SELF'].'?deletefr=1&amp;frid='.$frid.'&amp;group_id='.$group_id ?>" method="post">
    		    <?php echo dgettext('gforge-plugin-novafrs','delete_warning'); ?>
    		    <p>
    		        <input type="checkbox" name="confirm_delete" value="1"><?php echo dgettext('gforge-plugin-novafrs','sure') ?><br />
        		<p>
    	    	<input type="submit" name="post_changes" value="<?php echo dgettext('gforge-plugin-novafrs','delete') ?>" /></p>
		    </form>
		</p>
		<?php

    }
    
}

?>
