<?php
/*
 * Extra tabs plugin
 *
 * Copyright 2005, Raphaël Hertzog
 * Copyright 2006-2009, Roland Mas
 * Copyright 2009, Alain Peyrat
 */

require_once ('../../../www/env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';

$group_id = getIntFromRequest ('group_id') ;
$index = getIntFromRequest ('index') ;

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// get current information
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
        exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
        exit_error('Error',$group->getErrorMessage());
}

db_begin();

// Calculate new index field
$res = db_query_params ('SELECT COUNT(*) as c FROM plugin_extratabs_main WHERE group_id = $1',
			array ($group_id)) ;
$row = db_fetch_array($res);
$newid = $row['c'] + 1;

$selected = 0; // No item selected by default

// Do work before displaying so that the result is immediately visible
if (getStringFromRequest ('addtab') != '') {
	$tab_name = htmlspecialchars(trim(getStringFromRequest ('tab_name')));
	$tab_url = htmlspecialchars(trim(getStringFromRequest ('tab_url')));
	$res = db_query_params ('INSERT INTO plugin_extratabs_main (group_id, index, tab_name, tab_url) VALUES ($1,$2,$3,$4)',
				array ($group_id,
				       $newid,
				       $tab_name,
				       $tab_url)) ;
	if (!$res || db_affected_rows($res) < 1) {
		$feedback = sprintf (_('Cannot insert new tab entry: %s'),
				      db_error());
	} else {
		$feedback = _('Tab added');
	}
} elseif (getStringFromRequest ('delete') != '') {
	$res = db_query_params ('DELETE FROM plugin_extratabs_main WHERE group_id=$1 AND index=$2',
				array ($group_id,
				       $index)) ;
	if (!$res || db_affected_rows($res) < 1) {
		$feedback = sprintf (_('Cannot delete tab entry: %s'),
				      db_error());
	} else {
		$res = db_query_params ('UPDATE plugin_extratabs_main SET index=index-1 WHERE group_id=$1 AND index > $2',
					array ($group_id,
					       $index)) ;
	}
} elseif (getStringFromRequest ('up') != '') {
	if ($index > 1) {
		$previous = $index - 1;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=0 WHERE group_id=$1 AND index=$2',
				       array ($group_id,
					      $index)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=$3',
				       array ($index,
					      $group_id,
					      $previous)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=0',
				       array ($previous,
					      $group_id)) ;
		$selected = $previous;
	} else {
	    $selected = $index;
	}
} elseif (getStringFromRequest ('down') != '') {
	if ($index < $newid - 1) {
		$next = $index + 1;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=0 WHERE group_id=$1 AND index=$2',
				       array ($group_id,
					      $index)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=$3',
				       array ($index,
					      $group_id,
					      $next)) ;
		$res = db_query_params('UPDATE plugin_extratabs_main SET index=$1 WHERE group_id=$2 AND index=0',
				       array ($next,
					      $group_id)) ;
		$selected = $next;
	} else {
	    $selected = $index;
	}
}
if (!$res) {
	db_rollback();
} else  {
	db_commit();
}

$adminheadertitle=sprintf(_('Project Admin: %1$s'), $group->getPublicName() );
project_admin_header(array('title'=>$adminheadertitle, 'group'=>$group->getID()));

?>

<p>&nbsp;</p>

<h3><?php echo _('Add new tab to project'); ?></h3>
<p><?php echo _("You can add to this project's menu bar new custom tabs linking to Web pages, with the form below.") ?></p>
<p />

<form name="new_tab" action="<?php echo util_make_url ('/plugins/extratabs/'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group->getID() ?>" />
<input type="hidden" name="addtab" value="1" />
	<strong><?php echo _('Name of the tab:') ?></strong>
<?php echo utils_requiredField(); ?><br/>
<input type="text" size="15" maxlength="255" name="tab_name" /><br/>
	<strong><?php echo _('URL of the link:') ?></strong>
<?php echo utils_requiredField(); ?><br/>
<input type="text" size="15" name="tab_url" value="http://" /><br/>
<input type="submit" value="<?php echo _('Add tab') ?>" />
</form>
<p />

<?php
	$res = db_query_params ('SELECT * FROM plugin_extratabs_main WHERE group_id=$1 ORDER BY index ASC', array ($group_id)) ;
$nbtabs = db_numrows($res) ;
if ($nbtabs > 0) {
	
?>


	<h3><?php echo _('Manage extra tabs') ;?></h3>
<p>
	<?php echo _('You can move and delete the tabs that you already added. Please note that those extra tabs can only appear on the right of the standard tabs. And you can only move them inside the set of extra tabs.') ;

?></p>
<p />
<form name="change_tab" action="<?php echo util_make_url ('/plugins/extratabs/'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group->getID() ?>" />
<?php 
	echo _('Tab to modify:')
?>
<select name="index">
<?php
while ($row = db_fetch_array($res)) {
    if ($row['index'] == $selected) {
	echo "<option selected value='" . $row['index'] . "'>" . $row['tab_name'] .  "</option>";
    } else {
	echo "<option value='" . $row['index'] . "'>" . $row['tab_name'] .  "</option>";
    }
} ?>
</select><br/><br/>
	  <?php if ($nbtabs > 1) { ?>
<input type="submit" name="up" value="<?php echo _('Move tab left') ?>" /><br/>
<input type="submit" name="down" value="<?php echo _('Move tab right') ?>" /><br/>
		  <?php } ?>
<input type="submit" name="delete" value="<?php echo _('Delete tab') ?>" />
</form>
<p />

<?php
	  }
project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
