<?php
/**
  *
  * Module to render generic HTML tables for Site Admin
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */


/**
 *	admin_table_add() - present a form for adding a record to the specified table
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 */
function admin_table_add($table, $unit, $primary_key, $lang) {
	global $PHP_SELF;

	// This query may return no rows, but the field names are needed.
	$result = db_query('SELECT * FROM '.$table.' WHERE '.$primary_key.'=0');

	if ($result) {
		$cols = db_numfields($result);

		echo 'Create a new '.$unit.' below:
			<form name="add" action="'.$PHP_SELF.'?function=postadd&lang='.$lang.'" method="post">
			<table>';

		for ($i = 0; $i < $cols; $i++) {
			$fieldname = db_fieldname($result, $i);

			echo '<tr><td><strong>'.$fieldname.'</strong></td>';
			echo '<td><input type="text" name="'.$fieldname.'" value="" /></td></tr>';
		}
		echo '</table><input type="submit" value="Submit New '.ucwords($unit).'" /></form>
			<form name="cancel" action="'.$PHP_SELF.'" method="post">
			<input type="submit" value="Cancel" />
			</form>';
	} else {
		echo db_error();
	}
}

/**
 *	admin_table_postadd() - update the database based on a submitted change
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 */
function admin_table_postadd($table, $unit, $primary_key, $lang) {
	global $HTTP_POST_VARS;

	$sql = "INSERT INTO $table ("
		. join(',', array_keys($HTTP_POST_VARS))
		. ") VALUES ('"
		. htmlspecialchars(join("','", array_values($HTTP_POST_VARS)))
		. "')";

	if (db_query($sql)) {
		echo ucfirst($unit).' successfully added.';
	} else {
		echo db_error();
	}
}

/**
 *	admin_table_confirmdelete() - present a form to confirm requested record deletion
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 *	@param $id - the id of the record to act on
 */
function admin_table_confirmdelete($table, $unit, $primary_key, $id, $lang) {
	global $PHP_SELF;

	$result = db_query("SELECT * FROM $table WHERE $primary_key=$id AND language_id='".$lang."'");

	if ($result) {
		$cols = db_numfields($result);

		echo 'Are you sure you want to delete this '.$unit.'?
			<ul>';
		for ($i = 0; $i < $cols; $i++) {
			echo '<li><strong>'.db_fieldname($result,$i).'</strong> '.db_result($result,0,$i).'</li>';
		}
		echo '</ul>
			<form name="delete" action="'.$PHP_SELF.'?function=delete&lang='.$lang.'&amp;id='.$id.'" method="post">
			<input type="submit" value="Delete" />
			</form>
			<form name="cancel" action="'.$PHP_SELF.'" method="post">
			<input type="submit" value="Cancel" />
			</form>';
	} else {
		echo db_error();
	}
}

/**
 *	admin_table_delete() - delete a record from the database after confirmation
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 *	@param $id - the id of the record to act on
 */
function admin_table_delete($table, $unit, $primary_key, $id, $lang) {
	if (db_query("DELETE FROM $table WHERE $primary_key=$id AND language_id='".$lang."'")) {
		echo ucfirst($unit).' successfully deleted.';
	} else {
		echo db_error();
	}
}

/**
 *	admin_table_edit() - present a form for editing a record in the specified table
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 *	@param $id - the id of the record to act on
 */
function admin_table_edit($table, $unit, $primary_key, $id, $lang) {
	global $PHP_SELF;

	$query="SELECT * FROM $table WHERE $primary_key=$id AND language_id='".$lang."'";
	//$result = db_query("SELECT * FROM $table WHERE $primary_key=$id AND language_id='".$lang."'");
	//echo "$query<br>\n";
	$result = db_query($query);

	if ($result) {
		$cols = db_numfields($result);

		echo 'Modify the '.$unit.' below:
			<form name="edit" action="'.$PHP_SELF.'?function=postedit&lang='.$lang.'&amp;id='.$id.'" method="post">
			<table>';

		for ($i = 0; $i < $cols; $i++) {
			$fieldname = db_fieldname($result, $i);
			$value = db_result($result, 0, $i);

			echo '<tr><td><strong>'.$fieldname.'</strong></td>';

			if ($fieldname == $primary_key) {
				echo "<td>$value</td></tr>";
			} elseif ($fieldname =='tstring')  {
				echo '<td><textarea type="text" name="'.$fieldname.'" cols="50" rows="10"/>'.stripslashes($value).'</textarea></td></tr>';
			} else {
				echo '<td><input type="text" name="'.$fieldname.'" value="'.$value.'"/></td></tr>';
			}
		}
		echo '</table><input type="submit" value="Submit Changes" /></form>
			<form name="cancel" action="'.$PHP_SELF.'?function=show&lang='.$lang.'" method="post">
			<input type="submit" value="Cancel" />
			</form>';
	} else {
		echo db_error();
	}
}

/**
 *	admin_table_postedit() - update the database to reflect submitted modifications to a record
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 *	@param $id - the id of the record to act on
 */
//function admin_table_postedit($table, $unit, $primary_key, $id, $lang) {
function admin_table_postedit($table, $unit, $primary_key, $whereclause, $columns, $edit, $id, $lang) {
	global $HTTP_POST_VARS;

	$sql = 'UPDATE '.$table.' SET ';
	while (list($var, $val) = each($HTTP_POST_VARS)) {
		if ($var != $primary_key) {
			$sql .= "$var='". htmlspecialchars($val) ."', ";
		}
	}
	$sql = ereg_replace(', $', ' ', $sql);
	$sql .= "WHERE $primary_key=$id AND language_id='".$lang."'";

	if (db_query($sql)) {
		echo ucfirst($unit) . ' successfully modified.';
	} else {
		echo db_error();
	}
	//echo '
	//		<form name="cancel" action="'.$PHP_SELF.'?function=show&lang='.$lang.'" method="post">
	//		<input type="submit" value="Edit More" />
	//		</form>';
	echo admin_table_show($table, $unit, $primary_key, $whereclause, $columns, $edit, $lang);
}

/**
 *	admin_table_show() - display the specified table, sorted by the primary key, with links to add, edit, and delete
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 */
function admin_table_show($table, $unit, $primary_key, $whereclause, $columns, $edit, $lang) {
        global $HTML, $PHP_SELF;

	//CB// echo "<h1>SELECT * FROM $table $whereclause</h1>";
        $result = db_query("SELECT $columns FROM $table $whereclause;");

	if ($result) {
		$rows = db_numrows($result);
		$cols = db_numfields($result);
		if (! $edit) $cols .=-1;

                echo '<table border="0" width="100%">
		<tr bgcolor="'.$HTML->COLOR_HTMLBOX_TITLE.'">
		<td colspan="'.($cols+1).'"><strong><span style="color:'. $HTML->FONTCOLOR_HTMLBOX_TITLE .'">'. ucwords($unit) .'s</span></strong>';
		if ($edit) echo '<a href="'.$PHP_SELF.'?function=add&lang='.$lang.'">[add new]</a>';
		echo "</td></tr>\n";

		if ($edit) echo '
			<tr><td width="5%"></td>';
		else echo '
			<tr>';

                for ($i = 0; $i < $cols; $i++) {
			echo '<td><strong>'.db_fieldname($result,$i).'</strong></td>';
		}
		echo "</tr>\n";

                for ($j = 0; $j < $rows; $j++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '>';

                        $id = db_result($result,$j,0);
                        if ($edit) echo '<td><a href="'.$PHP_SELF.'?function=edit&lang='.$lang.'&amp;id='.$id.'">[edit]</a>';
                        if ($edit) echo '<a href="'.$PHP_SELF.'?function=confirmdelete&lang='.$lang.'&amp;id='.$id.'">[delete]</a> </td>';
			for ($i = 0; $i < $cols; $i++) {
				echo '<td>'. stripslashes(htmlspecialchars(db_result($result, $j, $i))) .'</td>';
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	} else {
		echo db_error();
	}
}


require_once('pre.php');
session_require(array('group'=>'1','admin_flags'=>'A'));

$HTML->header(array('title'=>'Edit the '. $lang .' Language '. ucwords($unit) .'s'));

echo '<h3>Edit the '. $lang .' Language ' . ucwords($unit) .'s</h3>
<p><a href="/admin/">Site Admin Home</a></p>
<p>&nbsp;</p>';

switch ($function) {
	case 'add' : {
		admin_table_add($table, $unit, $primary_key, $lang);
		break;
	}
	case 'postadd' : {
		admin_table_postadd($table, $unit, $primary_key, $lang);
		break;
	}
	case 'confirmdelete' : {
		admin_table_confirmdelete($table, $unit, $primary_key, $id, $lang);
		break;
	}
	case 'delete' : {
		admin_table_delete($table, $unit, $primary_key, $id, $lang);
		break;
	}
	case 'edit' : {
		admin_table_edit($table, $unit, $primary_key, $id, $lang);
		break;
	}
	case 'postedit' : {
		//admin_table_postedit($table, $unit, $primary_key, $id, $lang);
		echo admin_table_postedit($table, $unit, $primary_key, $whereclause, $columns, $edit, $id, $lang);
		break;
	}
	case 'show' : {
		echo admin_table_show($table, $unit, $primary_key, $whereclause, $columns, $edit, $lang);
		break;
	}
}


$HTML->footer(array());

?>
