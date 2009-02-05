<?php
/**
 * GForge Fr Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: index.php,v 1.14 2006/11/22 10:17:24 pascal Exp $
 */


/*
	File Manager

	by Quentin Cregan, SourceForge 06/2000

	Complete OO rewrite by Tim Perdue 1/2003
*/

require_once ("../../env.inc.php");
require_once ($gfwww."include/pre.php");
require_once ("plugins/novafrs/include/FileFactory.class.php");
require_once ("plugins/novafrs/include/FileGroupFrs.class.php");
require_once ("plugins/novafrs/include/FileGroupFactory.class.php");
require_once ("plugins/novafrs/include/FileConfig.class.php");
require_once ("plugins/novafrs/include/utils.php");
require_once ("plugins/novafrs/include/FileGroupHTML.class.php");

if (!$group_id) {
	exit_no_group();
}

$g =& group_get_object ($group_id);
if (!$g || !is_object($g) || $g->isError()) {
	exit_no_group();
}

$perm =& $g->getPermission( session_get_user() );
if (!$perm || $perm->isError() || !$perm->isReleaseTechnician()) {
	exit_permission_denied();
}

$auth = new FileGroupAuth( $group_id, $LUSER );

$upload_dir = $sys_ftp_upload_dir . "/" . $g->getUnixName();
$config = FileConfig::getInstance();

//
//
//	Submit the changes to the database
//
//

if ($submit) {
	if ($editgroup) {
	    
    	if( !$auth->canWrite( $fr_group ) or !$auth->canWrite( $parent_fr_group ) ){
	        exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','noWriteAuth'));
	    }
	    	    
	    
		$dg = new FileGroupFrs($g,$fr_group);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}
		
		$oldPath = $dg->getPath();
		
		
		if (!$dg->update($groupname,$parent_fr_group)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		
		$newPath = $dg->getPath();

		$oldPath = $config->sys_novafrs_path . '/' . $g->getUnixName () . '/' . $oldPath;
		$newPath = $config->sys_novafrs_path . '/' . $g->getUnixName() . '/' . $newPath;
		
		if( !rename( $oldPath, $newPath ) ){
		    exit_error('Error', "can't move directory" );
		}
		
		$feedback = dgettext('general','update_successful');


	} elseif ($addgroup) {
		$dg = new FileGroupFrs($g);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}

    	if( !$auth->canWrite( $parent_fr_group ) ){
	        exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','noWriteAuth'));
	    }

		if (!$dg->create($groupname, $parent_fr_group)) {
			exit_error('Error',$dg->getErrorMessage());
		}
		
		$feedback = dgettext('general','create_successful');
	
	} else if( $delgroup and $sure ){
	    $feedback = dgettext('general','deleted');
	    
	    $dgf = new FileGroupFactory($g);
    	if ($dgf->isError()) {
	    	exit_error('Error',$dgf->getErrorMessage());
	    }

		$dg = new FileGroupFrs($g, $fr_group);
		if ($dg->isError()) {
			exit_error('Error',$dg->getErrorMessage());
		}

    	if( !$auth->canDelete( $dg->getParentID() ) ){
	        exit_error(dgettext('general','error'),dgettext('gforge-plugin-novafrs','noDeleteAuth'));
	    }

        $dirPath = $config->sys_novafrs_path . '/' . $g->getUnixName () . '/' . $dg->getPath ();

	    if( !$dgf->delete_group( $fr_group ) ){
            exit_error('Error',$dgf->getErrorMessage());
	    }

        header('Location: index.php?addgroup=1&group_id='.$group_id.'&feedback='.urlencode($feedback));
        exit;
    }
}


//
//
//	Add a file group / view existing groups list
//
//
if ($addgroup) {

	novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_admin'));

	echo "<h1>" . dgettext('gforge-plugin-novafrs','section_admin_groups') . "</h1>";
	
	$dgf = new FileGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new FileGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}
	
	$nested_groups =& $dgf->getNested();
	
	if (count($nested_groups) > 0) {
		$title_arr=array();
		$title_arr[]=dgettext('gforge-plugin-novafrs','group_id');
		$title_arr[]=dgettext('gforge-plugin-novafrs','group_name');

		echo $GLOBALS['HTML']->listTableTop ($title_arr);
		
		$row = 0;
		$dgh->showTableNestedGroups($nested_groups, $row);
		
		echo $GLOBALS['HTML']->listTableBottom();
		
	} else {
		echo "\n<h1>".dgettext('gforge-plugin-novafrs','error_no_groups_defined')."</h1>";
	}
	?>
	<p><strong><?php echo dgettext('gforge-plugin-novafrs','add_group') ?>:</strong></p>
	<form name="addgroup" action="index.php?addgroup=1&amp;group_id=<?php echo $group_id; ?>" method="post">
	<table>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novafrs','new_group_name') ?>:</th>
			<td><input type="text" name="groupname" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novafrs','new_group_parent') ?>:</th>
			<td>
				<?php echo $dgh->showSelectNestedGroups($nested_groups, 'parent_fr_group') ?>
			</td>

			<td><input type="submit" value="<?php echo dgettext('general','add') ?>" name="submit" /></td>
		</tr>
	</table>
	<p>
		 <?php echo dgettext('gforge-plugin-novafrs','editgroups_description') ?>
	</p>
	</form>
	<?php

	novafrs_footer ();

//
//
//	Edit a specific fr group
//
//
} elseif ($editgroup && $fr_group) {

	$dg = new FileGroupFrs($g,$fr_group);
	if ($dg->isError()) {
		exit_error('Error',$dg->getErrorMessage());
	}
	
	$dgf = new FileGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new FileGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}

	novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_admin'));
	?>
	<p><strong><?php echo dgettext('gforge-plugin-novafrs','edit_group') ?></strong></p>
	<form name="editgroup" action="index.php?editgroup=1&amp;group_id=<?php echo $group_id; ?>" method="post">
	<input type="hidden" name="fr_group" value="<?php echo $fr_group; ?>" />
	<table>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novafrs','group_name') ?>:</th>
			<td><input type="text" name="groupname" value="<?php echo $dg->getName(); ?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<th><?php echo dgettext('gforge-plugin-novafrs','group_parent') ?>:</th>
			<td>
			<?php
				$dgh->showSelectNestedGroups($dgf->getNested(), "parent_fr_group", true, $dg->getParentId(), array($dg->getID()));
			?>
			</td>
			<td><input type="submit" value="<?php echo dgettext('general','edit') ?>" name="submit" /></td>
		</tr>
	</table>
	<p>
		 <?php echo dgettext('gforge-plugin-novafrs','editgroups_description') ?>

	</p>
	</form>
	<a href="index.php?delgroup=1&amp;group_id=<?php echo $group_id; ?>&amp;fr_group=<?php echo $fr_group; ?>" >
	    <?php echo dgettext('gforge-plugin-novafrs','delete_group') ?>
	</a>
	<?php
	novafrs_footer ();
} else if ($deletefr && $frid) {
	$d= new File($g,$frid);
	if ($d->isError()) {
		exit_error('Error',$d->getErrorMessage());
	}
	
	novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_admin'));
?>
		<p>
		<form action="<?php echo $PHP_SELF.'?deletefr=1&amp;frid='.$d->getID().'&amp;group_id='.$d->Group->getID() ?>" method="post">
		<input type="hidden" name="submit" value="1" /><br />
		<?php echo dgettext('gforge-plugin-novafrs','delete_warning'); ?>
		<p>
		<input type="checkbox" name="sure" value="1"><?php echo dgettext('gforge-plugin-novafrs','sure') ?><br />
		<input type="checkbox" name="really_sure" value="1"><?php echo dgettext('gforge-plugin-novafrs','really_sure') ?><br />
		<p>
		<input type="submit" name="post_changes" value="<?php echo dgettext('gforge-plugin-novafrs','delete') ?>" /></p>
		</form></p>
<?php
	novafrs_footer ();




// confirm delete a group
} else if( $delgroup ){
    novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_admin'));    
    ?>
		<p>
		<form action="index.php?delgroup=1&amp;group_id=<?php echo $group_id; ?>&amp;fr_group=<?php echo $fr_group; ?>" method="post">
		<input type="hidden" name="submit" value="1" /><br />
		<?php echo dgettext('gforge-plugin-novafrs','delete_warning_group'); ?>
		<p>
		<input type="checkbox" name="sure" value="1"><?php echo dgettext('gforge-plugin-novafrs','sure') ?><br />
		<p>
		<input type="submit" name="post_changes" value="<?php echo dgettext('gforge-plugin-novafrs','delete') ?>" /></p>
		</form></p>
    <?php
	novafrs_footer ();    


//
//
//	Display the main admin page
//
//
} else {

	$df = new FileFactory($g);
	if ($df->isError()) {
		exit_error(dgettext('general','error'),$df->getErrorMessage());
	}
	
	$dgf = new FileGroupFactory($g);
	if ($dgf->isError()) {
		exit_error(dgettext('general','error'),$dgf->getErrorMessage());
	}
	

	$df->setStateID('ALL');
	$d_arr =& $df->getFiles();

	novafrs_header (dgettext ('gforge-plugin-novafrs', 'title_admin'));

	?>
	<h3><?php echo sprintf( dgettext ( 'gforge-plugin-novafrs' , 'section_admin_main' ) , $g->getPublicName ()) ?></h3>
	<p>
	<a href="index.php?group_id=<?php echo $group_id; ?>&amp;addgroup=1"><?php echo dgettext('gforge-plugin-novafrs','add_edit_frgroups') ?></a>
	</p>
	<?php
	
	if (!$d_arr || count($d_arr) < 1) {
		print "<p><strong>".dgettext('gforge-plugin-novafrs','error_no_frs').".</strong></p>";
	} else {
		// get a list of used file states
		$states = $df->getUsedStates();
		$nested_groups =& $dgf->getNested();
		echo "<ul>";
		foreach ($states as $state) {
			echo "<li><strong>".$state["name"]."</strong>";
			frman_display_files($nested_groups, $df, true, $state["stateid"], true);
			echo "</li>";
		}
		echo "</ul>";
	}

	novafrs_footer ();

}

?>
