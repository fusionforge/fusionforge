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
function admin_table_add($table, $unit, $primary_key) {
	global $PHP_SELF;

	// This query may return no rows, but the field names are needed.
	$result = db_query('SELECT * FROM '.$table.' WHERE '.$primary_key.'=0');

	if ($result) {
		$cols = db_numfields($result);

		echo 'Create a new '.$unit.' below:
			<form name="add" action="'.$PHP_SELF.'?function=postadd" method="post">
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
function admin_table_postadd($table, $unit, $primary_key) {
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
function admin_table_confirmdelete($table, $unit, $primary_key, $id) {
	global $PHP_SELF;

	$result = db_query("SELECT * FROM $table WHERE $primary_key=$id");

	if ($result) {
		$cols = db_numfields($result);

		echo 'Are you sure you want to delete this '.$unit.'?
			<ul>';
		for ($i = 0; $i < $cols; $i++) {
			echo '<li><strong>'.db_fieldname($result,$i).'</strong> '.db_result($result,0,$i).'</li>';
		}
		echo '</ul>
			<form name="delete" action="'.$PHP_SELF.'?function=delete&amp;id='.$id.'" method="post">
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
function admin_table_delete($table, $unit, $primary_key, $id) {
	if (db_query("DELETE FROM $table WHERE $primary_key=$id")) {
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
function admin_table_edit($table, $unit, $primary_key, $id) {
	global $PHP_SELF;

	$result = db_query("SELECT * FROM $table WHERE $primary_key=$id");

	if ($result) {
		$cols = db_numfields($result);

		echo 'Modify the '.$unit.' below:
			<form name="edit" action="'.$PHP_SELF.'?function=postedit&amp;id='.$id.'" method="post">
			<table>';

		for ($i = 0; $i < $cols; $i++) {
			$fieldname = db_fieldname($result, $i);
			$value = db_result($result, 0, $i);

			echo '<tr><td><strong>'.$fieldname.'</strong></td>';

			if ($fieldname == $primary_key) {
				echo "<td>$value</td></tr>";
			} else {
				echo '<td><input type="text" name="'.$fieldname.'" value="'.$value.'" /></td></tr>';
			}
		}
		echo '</table><input type="submit" value="Submit Changes" /></form>
			<form name="cancel" action="'.$PHP_SELF.'" method="post">
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
function admin_table_postedit($table, $unit, $primary_key, $id) {
	global $HTTP_POST_VARS;

	$sql = 'UPDATE '.$table.' SET ';
	while (list($var, $val) = each($HTTP_POST_VARS)) {
		if ($var != $primary_key) {
			$sql .= "$var='". htmlspecialchars($val) ."', ";
		}
	}
	$sql = ereg_replace(', $', ' ', $sql);
	$sql .= "WHERE $primary_key=$id";

	if (db_query($sql)) {
		echo ucfirst($unit) . ' successfully modified.';
	} else {
		echo db_error();
	}
}

/**
 *	admin_table_show() - display the specified table, sorted by the primary key, with links to add, edit, and delete
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 */
function admin_table_show($table, $unit, $primary_key) {
        global $HTML, $PHP_SELF;

        $result = db_query("SELECT * FROM $table ORDER BY $primary_key");

	if ($result) {
		$rows = db_numrows($result);
		$cols = db_numfields($result);

		$cell_data=array();
		$cell_data[]=array(ucwords($unit).'<a href="'.$PHP_SELF.'?function=add">[add new]</a>',
			'colspan="'.($cols+1).'"');

                echo '<table border="0" width="100%">';
		echo $HTML->multiTableRow('',$cell_data, TRUE);

                echo '
			<tr><td width="5%"></td>';
                for ($i = 0; $i < $cols; $i++) {
			echo '<td><strong>'.db_fieldname($result,$i).'</strong></td>';
		}
		echo '</tr>';

                for ($j = 0; $j < $rows; $j++) {
			echo '<tr '. $HTML->boxGetAltRowStyle($j) . '>';

                        $id = db_result($result,$j,0);
                        echo '<td><a href="'.$PHP_SELF.'?function=edit&amp;id='.$id.'">[edit]</a>';
                        echo '<a href="'.$PHP_SELF.'?function=confirmdelete&amp;id='.$id.'">[delete]</a> </td>';
			for ($i = 0; $i < $cols; $i++) {
				echo '<td>'. db_result($result, $j, $i) .'</td>';
			}
			echo '</tr>';
		}
		echo '</table>';
	} else {
		echo db_error();
	}
}


require_once('pre.php');
session_require(array('group'=>'1','admin_flags'=>'A'));

$HTML->header(array('title'=>'Edit the '. ucwords($unit) .'s Table'));

echo '<h3>Edit '. ucwords($unit) .'s</h3>
<p><a href="/admin/">Site Admin Home</a></p>
<p>&nbsp;</p>';

switch ($function) {
	case 'add' : {
		admin_table_add($table, $unit, $primary_key);
		break;
	}
	case 'postadd' : {
		admin_table_postadd($table, $unit, $primary_key);
		break;
	}
	case 'confirmdelete' : {
		admin_table_confirmdelete($table, $unit, $primary_key, $id);
		break;
	}
	case 'delete' : {
		admin_table_delete($table, $unit, $primary_key, $id);
		break;
	}
	case 'edit' : {
		admin_table_edit($table, $unit, $primary_key, $id);
		break;
	}
	case 'postedit' : {
		admin_table_postedit($table, $unit, $primary_key, $id);
		break;
	}
}

echo admin_table_show($table, $unit, $primary_key);

$HTML->footer(array());

?>
