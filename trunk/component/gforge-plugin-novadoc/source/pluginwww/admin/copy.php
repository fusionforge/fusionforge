<?php
/**
 * GForge Doc Mgr Facility
 *
 * Copyright 2002 GForge, LLC
 * http://gforge.org/
 *
 * @version   $Id: copy.php,v 1.5 2006/11/22 10:17:24 pascal Exp $
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




/**
 * Diplay choice of the branch to be copied and the name of the new branch
 */
function branchChoice( $g, $doc_group=0, $title='' ){
    global $Language;

    novadoc_header (dgettext('gforge-plugin-novadoc','title_admin'));

    $dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}
	
	$dgh = new DocumentGroupHTML($g);
	if ($dgh->isError()) {
		exit_error('Error',$dgh->getErrorMessage());
	}

    ?>
    <form action="" method="post">
    <table border="0" width="75%">
    <tr>
		<td>
		<strong> <?= dgettext('gforge-plugin-novadoc','copyBranchToCopy'); ?> : </strong> <br />
		<?php
		    $dgh->showSelectNestedGroups($dgf->getNested(), 'doc_group', false, $doc_group, array(), 1 );// $d->getDocGroupID());
		?>
    	</td>
	</tr>
	<tr>
		<td><br />
		<strong>	<?= dgettext('gforge-plugin-novadoc','copyBranchName'); ?> : </strong> <span><font color="red">*</font></span><br />
		<input type="text" name="title" value="<?=$title?>" size="40" maxlength="255" />
		</td>
	</tr>

	</table>
    <br />
	<input type="submit"  value="	Soumettre les informations " name="newBranch" />
	</form>
	<?php                    
}



// post the form
if( isset($newBranch) && $newBranch ){
    if( trim($title) == '' ){
        exit_missing_param();
    }
    $dgf = new DocumentGroupFactory($g);
	if ($dgf->isError()) {
		exit_error('Error',$dgf->getErrorMessage());
	}    
    if( !$dgf->copyArborescence( $doc_group, $title ) ){
        exit_error('Can\'t copy branch : ',$dgf->getErrorMessage());
    }
    session_redirect('/plugins/novadoc/?group_id='.$group_id);
}else{
    // display the form
    branchChoice($g);
    novadoc_footer ();
}

?>
