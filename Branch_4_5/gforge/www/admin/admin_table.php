<?php
/**
 * Module to render generic HTML tables for Site Admin
 *
 * Copyright 1999-2001 (c) VA Linux Systems
 *
 * @version   $Id$
 *
 * This file is part of GForge.
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
 * along with GForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


/**
 *	admin_table_add() - present a form for adding a record to the specified table
 *
 *	@param $table - the table to act on
 *	@param $unit - the name of the "units" described by the table's records
 *	@param $primary_key - the primary key of the table
 */
function admin_table_add($table, $unit, $primary_key) {
	global $PHP_SELF, $Language;

	// This query may return no rows, but the field names are needed.
	$result = db_query('SELECT * FROM '.$table.' WHERE '.$primary_key.'=0');

	if ($result) {
		$cols = db_numfields($result);

		echo $Language->getText('admin_admin_table', 'create_new_below', array(getUnitLabel($unit))).'
			<form name="add" action="'.$PHP_SELF.'?function=postadd" method="post">
			<table>';

		for ($i = 0; $i < $cols; $i++) {
			$fieldname = db_fieldname($result, $i);

			echo '<tr><td><strong>'.$fieldname.'</strong></td>';
			echo '<td><input type="text" name="'.$fieldname.'" value="" /></td></tr>';
		}
		echo '</table><input type="submit" value="'.$Language->getText('general', 'add').'" /></form>
			<form name="cancel" action="'.$PHP_SELF.'" method="post">
			<input type="submit" value="'.$Language->getText('general', 'cancel').'" />
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
	global $HTTP_POST_VARS, $Language;

	$sql = "INSERT INTO $table ("
		. join(',', array_keys($HTTP_POST_VARS))
		. ") VALUES ('"
		. htmlspecialchars(join("','", array_values($HTTP_POST_VARS)))
		. "')";

	if (db_query($sql)) {
		echo $Language->getText('admin_admin_table', 'successfully_added', array(ucfirst(getUnitLabel($unit))));
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
	global $PHP_SELF, $Language;
	
	if ($unit == "processor") {
		$result = db_numrows(db_query("SELECT processor_id FROM frs_file WHERE processor_id = $id"));
		if ($result > 0) {
			echo '<p>'.$Language->getText('admin_admin_table', 'cant_delete_processor', array(db_result(db_query("select name from frs_processor where processor_id = $id"),0,0))).'</p>';
			return;
		}
	}
	if ($unit == "license") {
		$result = db_numrows(db_query("SELECT license FROM groups WHERE license = $id"));
		if ($result > 0) {
			echo '<p>'.$Language->getText('admin_admin_table', 'cant_delete_license', array(db_result(db_query("select license_name from licenses where license_id = $id"),0,0))).'</p>';
			return;
		}
	}
	if ($unit == "supported_language") {
		$result = db_numrows(db_query('SELECT language FROM users WHERE language='.$id));
		if ($result > 0) {
			echo '<p>'.$Language->getText('admin_admin_table', 'cant_delete_language', array(db_result(db_query("select license_name from licenses where license_id = $id"),0,0))).'</p>';
			return;
		}
	}

	$result = db_query("SELECT * FROM $table WHERE $primary_key=$id");

	if ($result) {
		$cols = db_numfields($result);

		echo $Language->getText('admin_admin_table', 'delete_confirm', array(getUnitLabel($unit))).'<ul>';
		for ($i = 0; $i < $cols; $i++) {
			echo '<li><strong>'.db_fieldname($result,$i).'</strong> '.db_result($result,0,$i).'</li>';
		}
		echo '</ul>
			<form name="delete" action="'.$PHP_SELF.'?function=delete&amp;id='.$id.'" method="post">
			<input type="submit" value="'.$Language->getText('general', 'delete').'" />
			</form>
			<form name="cancel" action="'.$PHP_SELF.'" method="post">
			<input type="submit" value="'.$Language->getText('general', 'cancel').'" />
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
	global $Language;

	$sql = "DELETE FROM $table WHERE $primary_key=$id";
	if (db_query($sql)) {
		echo $Language->getText('admin_admin_table', 'successfully_deleted', array(ucfirst(getUnitLabel($unit))));
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
	global $PHP_SELF, $Language;

	$result = db_query("SELECT * FROM $table WHERE $primary_key=$id");

	if ($result) {
		$cols = db_numfields($result);

		echo $Language->getText('admin_admin_table', 'modify_below', array(getUnitLabel($unit))).'
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
		echo '</table><input type="submit" value="'.$Language->getText('general', 'submit').'" /></form>
			<form name="cancel" action="'.$PHP_SELF.'" method="post">
			<input type="submit" value="'.$Language->getText('general', 'cancel').'" />
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
	global $HTTP_POST_VARS, $Language;

	$sql = 'UPDATE '.$table.' SET ';
	while (list($var, $val) = each($HTTP_POST_VARS)) {
		if ($var != $primary_key) {
			$sql .= "$var='". htmlspecialchars($val) ."', ";
		}
	}
	$sql = ereg_replace(', $', ' ', $sql);
	$sql .= "WHERE $primary_key=$id";

	if (db_query($sql)) {
		echo $Language->getText('admin_admin_table', 'successfully_modified', array(ucfirst(getUnitLabel($unit))));
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
        global $HTML, $PHP_SELF, $Language;

        $result = db_query("SELECT * FROM $table ORDER BY $primary_key");

	if ($result) {
		$rows = db_numrows($result);
		$cols = db_numfields($result);

		$cell_data=array();
		$cell_data[]=array(ucwords(getUnitLabel($unit)).' <a href="'.$PHP_SELF.'?function=add">['.$Language->getText('admin_admin_table', 'add_new').']</a>',
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
                        echo '<td><a href="'.$PHP_SELF.'?function=edit&amp;id='.$id.'">['.$Language->getText('general', 'edit').']</a>';
                        echo '<a href="'.$PHP_SELF.'?function=confirmdelete&amp;id='.$id.'">['.$Language->getText('general', 'delete').']</a> </td>';
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

/**
 * getUnitLabel - returns the localized label of a unit
 *
 * @param string unit name
 * @return name localized label
 */
function getUnitLabel($unit) {
	global $Language;
	$label = $Language->getText('admin_admin_table', 'unit_'.$unit);
	return $label;
}


require_once('pre.php');
session_require(array('group'=>'1','admin_flags'=>'A'));

$HTML->header(array('title'=>$Language->getText('admin_admin_table', 'title', array(ucwords(getUnitLabel($unit))))));

echo '<h3>'.$Language->getText('admin_admin_table', 'title', array(ucwords(getUnitLabel($unit)))).'</h3>
<p><a href="/admin/">'.$Language->getText('admin_admin_table', 'site_admin_home').'</a></p>
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
