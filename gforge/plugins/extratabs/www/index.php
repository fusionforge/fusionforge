<?php
/*
 * Hello world plugin
 *
 * Roland Mas <lolando@debian.org>
 */

require_once ('../../../www/env.inc.php');
require_once $gfwww.'include/pre.php';
require_once $gfwww.'project/admin/project_admin_utils.php';
require_once $gfcommon.'/include/FusionForge.class.php';

$group_id = getIntFromRequest ('group_id') ;

session_require(array('group'=>$group_id,'admin_flags'=>'A'));

// get current information
$group =& group_get_object($group_id);
if (!$group || !is_object($group)) {
        exit_error('Error','Could Not Get Group');
} elseif ($group->isError()) {
        exit_error('Error',$group->getErrorMessage());
}

$perm =& $group->getPermission( session_get_user() );
if (!$perm || !is_object($perm)) {
        exit_error('Error','Could Not Get Permission');
} elseif ($perm->isError()) {
        exit_error('Error',$perm->getErrorMessage());
}

if (!$perm->isAdmin()) {
        exit_permission_denied();
}

// Calculate new index field
$res = db_query("SELECT COUNT(*) as c FROM plugin_extratabs_main 
		 WHERE group_id = '$group_id'");
$row = db_fetch_array($res);
$newid = $row['c'] + 1;

$selected = 0; // No item selected by default
$index = getIntFromRequest ('index') ;

// Do work before displaying so that the result is immediately visible
if (getStringFromRequest ('addtab') != '') {
	$tab_name = addslashes (getStringFromRequest ('tab_name')) ;
	$tab_url = addslashes (getStringFromRequest ('tab_url')) ;
	$res = db_query("INSERT INTO plugin_extratabs_main (group_id, index, tab_name, tab_url)
		  	 VALUES('$group_id','$newid','$tab_name','$tab_url')");

	if (!$res || db_affected_rows($res) < 1) {
		$feedback = sprintf (_('Cannot insert new tab entry: %s'),
				      db_error());
	} else {
		$feedback = _('Tab added');
	}
} elseif (getStringFromRequest ('delete') != '') {
	$res = db_query("DELETE FROM plugin_extratabs_main
			 WHERE group_id='$group_id' AND
			 index='$index'");
	if (!$res || db_affected_rows($res) < 1) {
		$feedback = sprintf (_('Cannot delete tab entry: %s'),
				      db_error());
	} else {
		$res = db_query("UPDATE plugin_extratabs_main
			 SET index = index - 1
			 WHERE group_id = '$group_id' AND
			 index > $index");
	}
  } elseif (getStringFromRequest ('up') != '') {
	if ($index > 1) {
		$previous = $index - 1;
		$res = db_query("
		    UPDATE plugin_extratabs_main
		    SET index = 0 
		    WHERE group_id = '$group_id' AND index = $index
		");
		$res = db_query("
		    UPDATE plugin_extratabs_main
		    SET index = $index 
		    WHERE group_id = '$group_id' AND index = $previous
		");
		$res = db_query("
		    UPDATE plugin_extratabs_main
		    SET index = $previous
		    WHERE group_id = '$group_id' AND index = 0
		");
		$selected = $previous;
	} else {
	    $selected = $index;
	}
} elseif (getStringFromRequest ('down') != '') {
	if ($index < $newid - 1) {
		$next = $index + 1;
		$res = db_query("
		    UPDATE plugin_extratabs_main
		    SET index = 0 
		    WHERE group_id = '$group_id' AND index = $index
		");
		$res = db_query("
		    UPDATE plugin_extratabs_main
		    SET index = $index 
		    WHERE group_id = '$group_id' AND index = $next
		");
		$res = db_query("
		    UPDATE plugin_extratabs_main
		    SET index = $next
		    WHERE group_id = '$group_id' AND index = 0
		");
		$selected = $next;
	} else {
	    $selected = $index;
	}
}

$adminheadertitle=sprintf(_('Project Admin: %1$s'), $group->getPublicName() );
project_admin_header(array('title'=>$adminheadertitle, 'group'=>$group->getID()));

?>

<p>&nbsp;</p>

<h3><?php echo _('Add new tabs'); ?></h3>
<p><?php echo _('You can add your own tabs in the menu bar with the form below.') ?></p>
<p>

<form name="new_tab" action="<?php echo util_make_url ('/plugins/extratabs/'); ?>" method="post">
<input type="hidden" name="group_id" value="<?php echo $group->getID() ?>" />
<input type="hidden" name="addtab" value="1" />
<input type="hidden" name="newid" value="<?php echo $newid ?>" />
	<strong><?php echo _('Name of the tab:') ?></strong>
<?php echo utils_requiredField(); ?><br/>
<input type="text" size="15" maxlength="255" name="tab_name" /><br/>
	<strong><?php echo _('URL of the tab:') ?></strong>
<?php echo utils_requiredField(); ?><br/>
<input type="text" size="15" name="tab_url" value="http://" /><br/>
<input type="submit" value="<?php echo _('Add tab') ?>" />
</form>
</p>

<?php
	$res = db_query("SELECT * FROM plugin_extratabs_main WHERE group_id='$group_id' ORDER BY index ASC");
$nbtabs = db_numrows($res) ;
if ($nbtabs > 0) {
	
?>


	<h3><?php echo _('Manage extra tabs') ;?></h3>
<p>
	<?php echo _('You can move and delete the tabs that you already added. Please note that those extra tabs can only appear on the right of the standard tabs. And you can only move them inside the set of extra tabs.') ;

?></p><p>
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
</p>

<?php
	  }
project_admin_footer(array());

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
