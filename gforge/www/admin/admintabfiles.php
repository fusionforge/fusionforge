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
			<FORM NAME="add" ACTION="'.$PHP_SELF.'?function=postadd" METHOD="POST">
			<TABLE>';

		for ($i = 0; $i < $cols; $i++) {
			$fieldname = db_fieldname($result, $i);

			echo '<TR><TD><B>'.$fieldname.'</B></TD>';
			echo '<TD><INPUT TYPE="text" NAME="'.$fieldname.'" VALUE=""></TD></TR>';
		}
		echo '</TABLE><INPUT TYPE="submit" VALUE="Submit New '.ucwords($unit).'"></FORM>
			<FORM NAME="cancel" ACTION="'.$PHP_SELF.'" method="POST">
			<INPUT TYPE="submit" VALUE="Cancel">
			</FORM>';
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
			<UL>';
		for ($i = 0; $i < $cols; $i++) {
			echo '<LI><B>'.db_fieldname($result,$i).'</B> '.db_result($result,0,$i).'</LI>';
		}
		echo '</UL>
			<FORM NAME="delete" ACTION="'.$PHP_SELF.'?function=delete&id='.$id.'" method="POST">
			<INPUT TYPE="submit" VALUE="Delete">
			</FORM>
			<FORM NAME="cancel" ACTION="'.$PHP_SELF.'" method="POST">
			<INPUT TYPE="submit" VALUE="Cancel">
			</FORM>';
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
			<FORM NAME="edit" ACTION="'.$PHP_SELF.'?function=postedit&id='.$id.'" METHOD="POST">
			<TABLE>';

		for ($i = 0; $i < $cols; $i++) {
			$fieldname = db_fieldname($result, $i);
			$value = db_result($result, 0, $i);

			echo '<TR><TD><B>'.$fieldname.'</B></TD>';
			
			if ($fieldname == $primary_key) {
				echo "<TD>$value</TD></TR>";
			} else {
				echo '<TD><INPUT TYPE="text" NAME="'.$fieldname.'" VALUE="'.$value.'"></TD></TR>';
			}
		}
		echo '</TABLE><INPUT TYPE="submit" VALUE="Submit Changes"></FORM>
			<FORM NAME="cancel" ACTION="'.$PHP_SELF.'" method="POST">
			<INPUT TYPE="submit" VALUE="Cancel">
			</FORM>';
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
function admin_table_show($table, $unit, $primary_key, $whereclause, $columns, $edit) {
        global $HTML, $PHP_SELF;

	//CB// echo "<H1>SELECT * FROM $table $whereclause</H1>";
        $result = db_query("SELECT $columns FROM $table $whereclause;");

	if ($result) {
		$rows = db_numrows($result);
		$cols = db_numfields($result);
		if (! $edit) $cols .=-1;

                echo '<TABLE BORDER="0" WIDTH="100%">
		<TR BGCOLOR="'.$HTML->COLOR_HTMLBOX_TITLE.'">
		<TD COLSPAN="'.($cols+1).'"><B><FONT COLOR="'. $HTML->FONTCOLOR_HTMLBOX_TITLE .'">'. ucwords($unit) .'s</FONT></B>';
		if ($edit) echo '<A HREF="'.$PHP_SELF.'?function=add">[add new]</A>';
		echo '</TD></TR>';

		if ($edit) echo '
			<TR><TD WIDTH="5%"></TD>';

                for ($i = 0; $i < $cols; $i++) {
			echo '<TD><B>'.db_fieldname($result,$i).'</B></TD>';
		}
		echo '</TR>';

                for ($j = 0; $j < $rows; $j++) {
			echo '<TR '. $HTML->boxGetAltRowStyle($j) . '">';

                        $id = db_result($result,$j,0);
                        if ($edit) echo '<TD><A HREF="'.$PHP_SELF.'?function=edit&id='.$id.'">[edit]</A>';
                        if ($edit) echo '<A HREF="'.$PHP_SELF.'?function=confirmdelete&id='.$id.'">[delete]</A> </TD>';
			for ($i = 0; $i < $cols; $i++) {
				echo '<TD>'. db_result($result, $j, $i) .'</TD>';
			}
			echo '</TR>';
		}
		echo '</TABLE>';
	} else {
		echo db_error();
	}
}


require_once('pre.php');
session_require(array('group'=>'1','admin_flags'=>'A'));

$HTML->header(array('title'=>'Edit the '. $lang .' Language '. ucwords($unit) .'s'));

echo '<H3>Edit the '. $lang .' Language ' . ucwords($unit) .'s</H3>
<P><A HREF="/admin/">Site Admin Home</A>
<P>';

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

echo admin_table_show($table, $unit, $primary_key, $whereclause, $columns, $edit);

$HTML->footer(array());

?>
