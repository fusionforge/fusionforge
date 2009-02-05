<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: index.php,v 1.14 2006/11/22 10:17:24 pascal Exp $
 */


/*
	Document Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novadoc/include/DocumentFactory.class.php");
require_once ("plugins/novadoc/include/DocumentGroupDocs.class.php");
require_once ("plugins/novadoc/include/DocumentGroupFactory.class.php");
require_once ("plugins/novadoc/include/DocumentConfig.class.php");
require_once ("plugins/novadoc/include/utils.php");
require_once ("plugins/novadoc/include/DocumentGroupHTML.class.php");

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

$auth = new DocumentGroupAuth( $group_id, $LUSER );

$upload_dir = $sys_ftp_upload_dir . "/" . $g->getUnixName();
$config = DocumentConfig::getInstance();



//
//
//	Submit the changes to the database
//
//

if ($submit) {
	if ($editgroup) {
	    
    	if( !$auth->canWrite( $doc_group ) or !$auth->canWrite( $parent_doc_group ) ){
	        exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','noWriteAuth'));
	    }
	    	    
	    
		$dg = new DocumentGroupDocs($g,$doc_group);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}
		
		$oldPath = $dg->getPath();
		
		
		if (!$dg->update($groupname,$parent_doc_group)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		
		$newPath = $dg->getPath();

		$oldPath = $config->sys_novadoc_path.'/'.$g->getUnixName().'/'.$oldPath;
		$newPath = $config->sys_novadoc_path.'/'.$g->getUnixName().'/'.$newPath;
		
		if( !rename( $oldPath, $newPath ) ){
		    exit_error('Error', "can't move directory" );
		}
		
		$feedback = dgettext('general','update_successful');


	} elseif ($addgroup) {
		$dg = new DocumentGroupDocs($g);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}

    	if( !$auth->canWrite( $parent_doc_group ) ){
	        exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','noWriteAuth'));
	    }

		if (!$dg->create($groupname, $parent_doc_group)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		
		$feedback = dgettext('general','create_successful');
	
	} else if( $delgroup and $sure ){
	    $feedback = dgettext('general','deleted');
	    
	    $dgf = new DocumentGroupFactory($g);
    	if ($dgf->isError()) {
	    	exit_error('Error',$dgf->getErrorMessage());
	    }

		$dg = new DocumentGroupDocs($g, $doc_group);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}

    	if( !$auth->canDelete( $dg->getParentID() ) ){
	        exit_error(dgettext('general','error'),dgettext('gforge-plugin-novadoc','noDeleteAuth'));
	    }

        $dirPath = $config->sys_novadoc_path.'/'.$g->getUnixName().'/'.$dg->getPath();        

	    if( !$dgf->delete_group( $doc_group ) ){
            exit_error('Error',$dgf->getErrorMessage());
	    }

        header('Location: index.php?addgroup=1&group_id='.$group_id.'&feedback='.urlencode($feedback));
        exit;
    }
}


//
//
//	Add a document group / view existing groups list
//
//
if ($addgroup) {

	novadoc_header (dgettext('gforge-plugin-novadoc','title_admin'));

	echo "<h1>".dgettext('gforge-plugin-novadoc','section_admin_groups')."</h1>";
	
	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new DocumentGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}
	
	$nested_groups =& $dgf->getNested();
	
	if (count($nested_groups) > 0) {
		$title_arr=array();
		$title_arr[]=dgettext('gforge-plugin-novadoc','group_id');
		$title_arr[]=dgettext('gforge-plugin-novadoc','group_name');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		
		$row = 0;
		$dgh->showTableNestedGroups($nested_groups, $row);
		
		echo $GLOBALS['HTML']->listTableBottom();
		
	} else {
		echo "\n<h1>".dgettext('gforge-plugin-novadoc','error_no_groups_defined')."</h1>";
	}
	?>
	<p><strong><?php echo dgettext('gforge-plugin-novadoc','add_group') ?>:</strong></p>
	<form name="addgroup" action="index.php?addgroup=1&amp;group_id=<?php echo $group_id; ?>" method="post">
	<table>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novadoc','new_group_name') ?>:</th>
			<td><input type="text" name="groupname" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novadoc','new_group_parent') ?>:</th>
			<td>
				<?php echo $dgh->showSelectNestedGroups($nested_groups, 'parent_doc_group') ?>
			</td>

			<td><input type="submit" value="<?php echo dgettext('general','add') ?>" name="submit" /></td>
		</tr>
	</table>
	<p>
		 <?php echo dgettext('gforge-plugin-novadoc','editgroups_description') ?>
	</p>
	</form>
	<?php

	novadoc_footer ();

//
//
//	Edit a specific doc group
//
//
} elseif ($editgroup && $doc_group) {

	$dg = new DocumentGroupDocs($g,$doc_group);
	if ($dg->isError()) {
		exit_error('Error',$dg->getErrorMessage());
	}
	
	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new DocumentGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}

	novadoc_header (dgettext('gforge-plugin-novadoc','title_admin'));
	?>
	<p><strong><?php echo dgettext('gforge-plugin-novadoc','edit_group') ?></strong></p>
	<form name="editgroup" action="index.php?editgroup=1&amp;group_id=<?php echo $group_id; ?>" method="post">
	<input type="hidden" name="doc_group" value="<?php echo $doc_group; ?>" />
	<table>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novadoc','group_name') ?>:</th>
			<td><input type="text" name="groupname" value="<?php echo $dg->getName(); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novadoc','group_parent') ?>:</th>
			<td>
			<?php
				$dgh->showSelectNestedGroups($dgf->getNested(), "parent_doc_group", true, $dg->getParentId(), array($dg->getID()));
			?>
			</td>
			<td><input type="submit" value="<?php echo dgettext('general','edit') ?>" name="submit" /></td>
		</tr>
	</table>
	<p>
		 <?php echo dgettext('gforge-plugin-novadoc','editgroups_description') ?>

	</p>
	</form>
	<a href="index.php?delgroup=1&amp;group_id=<?php echo $group_id; ?>&amp;doc_group=<?php echo $doc_group; ?>" >
	    <?php echo dgettext('gforge-plugin-novadoc','delete_group') ?>
	</a>
	<?php
	novadoc_footer ();
} else if ($deletedoc && $docid) {
	$d= new Document($g,$docid);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}
	
	novadoc_header (dgettext('gforge-plugin-novadoc','title_admin'));
?>
		<p>
		<form action="<?php echo $PHP_SELF.'?deletedoc=1&amp;docid='.$d->getID().'&amp;group_id='.$d->Group->getID() ?>" method="post">
		<input type="hidden" name="submit" value="1" /><br />
		<?php echo dgettext('gforge-plugin-novadoc','delete_warning'); ?>
		<p>
		<input type="checkbox" name="sure" value="1"><?php echo dgettext('gforge-plugin-novadoc','sure') ?><br />
		<input type="checkbox" name="really_sure" value="1"><?php echo dgettext('gforge-plugin-novadoc','really_sure') ?><br />
		<p>
		<input type="submit" name="post_changes" value="<?php echo dgettext('gforge-plugin-novadoc','delete') ?>" /></p>
		</form></p>
<?php
	novadoc_footer ();




// confirm delete a group
} else if( $delgroup ){
    novadoc_header (dgettext('gforge-plugin-novadoc','title_admin'));
    ?>
		<p>
		<form action="index.php?delgroup=1&amp;group_id=<?php echo $group_id; ?>&amp;doc_group=<?php echo $doc_group; ?>" method="post">
		<input type="hidden" name="submit" value="1" /><br />
		<?php echo dgettext('gforge-plugin-novadoc','delete_warning_group'); ?>
		<p>
		<input type="checkbox" name="sure" value="1"><?php echo dgettext('gforge-plugin-novadoc','sure') ?><br />
		<p>
		<input type="submit" name="post_changes" value="<?php echo dgettext('gforge-plugin-novadoc','delete') ?>" /></p>
		</form></p>
    <?php
	novadoc_footer ();    


//
//
//	Display the main admin page
//
//
} else {

	$df = new DocumentFactory($g);
	if ($df->isError()) {
		exit_error(dgettext('general','error'),$df->getErrorMessage());
	}
	
	$dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error(dgettext('general','error'),$dgf->getErrorMessage());
	}
	

	$df->setStateID('ALL');
	$d_arr =& $df->getDocuments();

	novadoc_header (dgettext ('gforge-plugin-novadoc','title_admin'));

	?>
	<h3><?php echo sprintf( dgettext ( 'gforge-plugin-novadoc' , 'section_admin_main' ) , $g->getPublicName ()) ?></h3>
	<p>
	<a href="index.php?group_id=<?php echo $group_id; ?>&amp;addgroup=1"><?php echo dgettext('gforge-plugin-novadoc','add_edit_docgroups') ?></a>
	</p>
	<?php
	
	if (!$d_arr || count($d_arr) < 1)
	{
		print "<p><strong>".dgettext('gforge-plugin-novadoc','error_no_docs').".</strong></p>";
	}
	else
	{
		// get a list of used document states
		$states = $df->getUsedStates();
		$nested_groups =& $dgf->getNested();
		echo "<ul>";
		foreach ($states as $state)
		{
			echo "<li><strong>".$state["name"]."</strong>";
			novadoc_display_documents ($nested_groups, $df, true, $state ["stateid"], true);
			echo "</li>";
		}
		echo "</ul>";
	}

	novadoc_footer ();

}

?>
