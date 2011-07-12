#! /usr/bin/php
<?php
/**
 * Class Generator for FusionForge
 *
 * Copyright 2005 (c) Francisco Gimeno
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require ('squal_pre.php');

function capitalize($name) {
	$tmp = str_replace ("_", " ", $name );
	$tmp2 = ucwords ($tmp);
	return str_replace(" ","", $tmp2);
}


function generateHeader($tableName,$author="Gforge Class Generator by Francisco Gimeno", $project="Gforge" ) {
	/* In the future, we should be able to read this from a file */
	$input = "<?php\n".
		"/**\n".
	 	" * FusionForge %{CLASS_NAME} Facility\n".
	 	" *\n".
	 	" * Copyright (c) %{YEAR} %{AUTHOR}\n".
	 	" *\n".
		" * This file is part of FusionForge. FusionForge is free software;\n".
		" * you can redistribute it and/or modify it under the terms of the\n".
		" * GNU General Public License as published by the Free Software\n".
		" * Foundation; either version 2 of the Licence, or (at your option)\n".
		" * any later version.\n".
		" *\n".
		" * FusionForge is distributed in the hope that it will be useful,\n".
		" * but WITHOUT ANY WARRANTY; without even the implied warranty of\n".
		" * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the\n".
		" * GNU General Public License for more details.\n".
		" *\n".
		" * You should have received a copy of the GNU General Public License along\n".
		" * with FusionForge; if not, write to the Free Software Foundation, Inc.,\n".
		" * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.\n".
 		" */\n".
		"\n".
		"/**\n".
		" * %{TITLE}\n".
		" * By %{AUTHOR}, %{YEAR}\n".
		" * Rewrite in OO and coding guidelines 12/2002 by Tim Perdue\n".
		" */\n".
		"\n".
		"require_once('common/include/Error.class');\n";
		"require_once('common/include/Validator.class');\n\n";

	$variables = array("%{CLASS_NAME}","%{YEAR}","%{AUTHOR}",
			"%{PROJECT}", "%{TITLE}" );
	$substitutions = array($tableName, date("Y"), $author, $project, capitalize($tableName) );
	$output = str_replace( $variables, $substitutions, $input );
	return $output;
}

function generate_GETOBJECT($tableName) {
	$input= "function &%{NAME}_get_object($%{ID_FIELD},\$data=false) {\n".
		"\tglobal $%{OBJECT};\n".
		"\tif (!isset($%{OBJECT}[\"_\".$%{ID_FIELD}.\"_\"])) {\n".
		"\t\tif (\$data) {\n".
                "\t\t\t//the db result handle was passed in\n".
		"\t\t} else {\n".
		"\t\t\t\$res=db_query(\"SELECT * FROM %{TABLE_NAME}\n".
		"\t\t\t\tWHERE %{ID_FIELD}='$%{ID_FIELD}'\");\n\n".
		"\t\t\tif (db_numrows(\$res) <1 ) {\n".
		"\t\t\t\t$%{OBJECT}[\"_\".$%{ID_FIELD}.\"_\"]=false;\n".
		"\t\t\t\treturn false;\n".
		"\t\t\t}\n".
		"\t\t\t\$data =& db_fetch_array(\$res);\n\n".
		"\t\t}\n".
		"\t\t//\$ProjectGroup =& projectgroup_get_object(\$data[\"group_project_id\"]);\n".
		"\t\t//$%{OBJECT}[\"_\".$%{ID_FIELD}.\"_\"]= new %{CLASS_NAME}(\$ProjectGroup,$%{ID_FIELD},\$data);\n".
                "\t\t$%{OBJECT}[\"_\".$%{ID_FIELD}.\"_\"]= new %{CLASS_NAME}($%{ID_FIELD},\$data);\n\n".
		"\t}\n\n".
                "\treturn $%{OBJECT}[\"_\".$%{ID_FIELD}.\"_\"];\n}\n\n";
	$variables = array("%{NAME}","%{OBJECT}","%{TABLE_NAME}", "%{ID_FIELD}", "%{CLASS_NAME}" );
	$reducedName=strtolower($tableName);
	$reducedName=str_replace ( "_", "", $reducedName );
	$objName = strtoupper($reducedName)."_OBJ";
	$substitutions = array($reducedName, $objName, $tableName, $tableName."_id", Capitalize($tableName));
	$output = str_replace($variables, $substitutions, $input);
	return $output;



}

function getIdFieldFromFieldsArr($fields) {
	$keys=array_keys($fields);
	return $keys[0];
}

function generateClassHead($className) {
	$output="class ".$className." extends Error {\n\n";

	return $output;
}

function generateClassVars($className) {
	$output="\t/**\n".
        	"\t * Associative array of data from db.\n".
        	"\t *\n".
        	"\t * @var  array   \$data_array.\n".
        	"\t */\n".
        	"\tvar \$data_array;\n".
        	"\t/**\n".
        	"\t * The ProjectGroup object.\n".
        	"\t *\n".
        	"\t * @var  object  \$ProjectGroup.\n".
        	"\t */\n".
        	"\t// var \$ProjectGroup;\n";
	return $output;
}

function generateClassConstructor($className, $fields) {
	$output="\t/**\n".
         "\t *  Constructor.\n".
         "\t *\n".
         "\t *  (@param  object 	The ProjectGroup object to which this object is associated.)\n".
         "\t *  @param  int 	The id.\n".
         "\t *  @param  array 	The associative array of data.\n".
         "\t *  @return boolean success.\n".
         "\t */\n";
	$output.="\tfunction ".$className."(/*&\$ProjectGroup,*/ \$".getIdFieldFromFieldsArr($fields)."=false, \$arr=false) {\n";
	$output.="\t\t\$this->error(); \n\n".
		"\t\t/*if (!\$ProjectGroup || !is_object(\$ProjectGroup)) {\n".
                "\t\t\t\$this->setError('$className:: No Valid ProjectGroup Object');\n".
                "\t\t\t\treturn false;\n".
                "\t\t}\n".
                "\t\tif (\$ProjectGroup->isError()) {\n".
                "\t\t\t\$this->setError('$className:: '.\$ProjectGroup->getErrorMessage());\n".
                "\t\t\t\treturn false;\n".
                "\t\t}\n".
                "\t\t\$this->ProjectGroup =& \$ProjectGroup;*/\n";
	$input="\t\tif ($%{ID_FIELD}) {\n".
                "\t\t\tif (!\$arr || !is_array(\$arr)) {\n".
                "\t\t\t\tif (!\$this->fetchData($%{ID_FIELD})) {\n".
                "\t\t\t\t\treturn false;\n".
                "\t\t\t\t}\n".
                "\t\t\t} else {\n".
                "\t\t\t\t\$this->data_array =& \$arr;\n".
                "\t\t\t\t//\n".
                "\t\t\t\t//      Verify this message truly belongs to this ProjectGroup\n".
                "\t\t\t\t//\n".
                "\t\t\t\t/*if (\$this->data_array['%{ID_FIELD}'] != \$this->ProjectGroup->getID()) {\n".
                "\t\t\t\t\t\$this->setError('Group_project_id in db result does not match ProjectG roup Object');\n".
                "\t\t\t\t\t\treturn false;\n".
                "\t\t\t\t}*/\n".
                "\t\t\t} \n".
                "\t\t}\n".
		"\t\treturn true;\n";
	$variables=array("%{ID_FIELD}","KKKK");
	$substitutions=array(getIdFieldFromFieldsArr($fields),"FFFF");

	$output.=str_replace($variables,$substitutions,$input);
	$output.="\t}\n\n";
	return $output;
}

function generateClassObjectCreator($tableName,$className,$fields) {
	/* Comments */
	$output="\t/**\n".
		"\t * create - create a new ".$className." in the database.\n\t *\n";
	foreach ($fields as $fieldName => $field ) {
		$output.="\t *\t@param	".$field["type"]." ".$fieldName.".\n";
	}
	$output.="\t * @return boolean Success.\n\t */\n";
	/* Declaration */
	$output.="\tfunction create(";
	$count = 0;
	$lineLength = strlen("    function create(");
	foreach ($fields as $fieldName => $field) {
		if($count++ != 0)
			$output.=",";  // First occurence hasn't a comma before
		$lineLength += strlen($fieldName );
		if ($lineLength >= 80 ) {
			$output.="\n\t\t"; // New line at 80
			$lineLength= 8;
		}
		$output.="\$".$fieldName;
	}
	$output.=") {\n";
	/* Validations */
	$output.="\t\t\$v = new Validator();\n";
	foreach ($fields as $fieldName => $field ) {
                $output.="\t\t\$v->check(\$".$fieldName.", \"".$fieldName."\");\n";
        }
        $output.="\t\tif (!\$v->isClean()) {\n".
                 "\t\t\t\$this->setError(\$v->formErrorMsg(\"Must include \"));\n".
                 "\t\t\treturn false;\n".
                "\t\t}\n";

	/* Perms Checks */
	$output.="\t\t/*  CHECK FOR PERMISSION\n".
		"\t\t\$perm =& \$this->ProjectGroup->Group->getPermission( session_get_user() );\n\n".
		"\t\tif (!\$perm || !is_object(\$perm) || !\$perm->isXXAdmin()) {\n".
                "\t\t\$this->setPermissionDeniedError();\n".
                "\t\t\treturn false;\n".
                "\t\t}*/\n";

	/* Hard Work: SQL Sentence */
	$input="\t\tdb_begin();\n\n".
               "\t\t\$res=db_query(\"SELECT nextval('%{TABLE_NAME}_pk_seq') AS id\");\n".
               "\t\tif (!$%{ID_FIELD}=db_result(\$res,0,'id')) {\n".
               "\t\t\t\$this->setError('Could Not Get Next ID');\n".
               "\t\t\tdb_rollback();\n".
               "\t\t\treturn false;\n".
               "\t\t} else {\n".
               "\t\t\t\$this->data_array['%{ID_FIELD}']=$%{ID_FIELD};\n".
		"\t\t\t\n".
               "\t\t\t/* SEVERAL CHECKS more\n".
	       "\t\t\tif (!\$this->setDependentOn(\$depend_arr)) {\n".
               "\t\t\t\tdb_rollback();\n".
               "\t\t\t\treturn false;\n".
               "\t\t\t} elseif (!\$this->setAssignedTo(\$assigned_arr)) {\n".
               "\t\t\t\tdb_rollback();\n".
               "\t\t\t\treturn false;\n".
               "\t\t\t} else { */\n".
               "\t\t\t\t\$sql=\"INSERT INTO %{TABLE_NAME} (%{ALL_FIELDS})\n".
               "\t\t\t\t\tVALUES (%{ALL_VALUES})\";\n";
	$count = 0;
	$field_len = 40;
	$values_len= 40;
	/* Preparing SQL sentence */
	foreach($fields as $fieldName => $field )
	{
		if ($count++ != 0 ) {
			$all_fields.=",";
			$all_values.=",";
		}
		$field_len += strlen($fieldName);
		if ($field_len > 80) {
			$all_fields.="\n\t\t\t\t\t";
			$field_len = 40;
		}
		$all_fields.=$fieldName;
		if ($field["type"] == "string" || $field["type"] == "bpchar" ) {
			$values_len += strlen ("'\".htmlspecialchars(\$".$fieldName.").\"'");
			if ($values_len > 80 ) {
				$all_values.="\n\t\t\t\t\t";
				$values_len = 40;
			}
			$all_values.="'\".htmlspecialchars(\$".$fieldName.").\"'";
		} else {
			$values_len += strlen ("'\".\$".$fieldName.".\"'");
			if ($values_len > 80 ) {
				$all_values.="\n\t\t\t\t\t";
				$values_len = 40;
			}
			$all_values.="'\".\$".$fieldName.".\"'";
		}
	}
	$variables = array ("%{TABLE_NAME}","%{ID_FIELD}","%{ALL_FIELDS}", "%{ALL_VALUES}","%{CLASSNAME}");
	$substitutions = array ( $tableName, getIdFieldFromFieldsArr($fields), $all_fields, $all_values, $className );
	$output.=str_replace($variables,$substitutions,$input);
	/* Check Results */
        $input="\t\t\t\t\$result=db_query(\$sql);\n".
        	"\t\t\t\tif (!\$result || db_affected_rows(\$result) < 1) {\n".
                "\t\t\t\t\t\$this->setError('%{CLASSNAME}::create() Posting Failed '.db_error());\n".
                "\t\t\t\t\tdb_rollback();\n".
                "\t\t\t\t\treturn false;\n".
                "\t\t\t\t} else {\n".
                "\t\t\t\t\tif (!\$this->fetchData(\$%{ID_FIELD})) {\n".
                "\t\t\t\t\t\tdb_rollback();\n".
                "\t\t\t\t\t\treturn false;\n".
                "\t\t\t\t\t} else {\n".
                "\t\t\t\t\t\t/* \$this->sendNotice(1); */\n".
                "\t\t\t\t\t\tdb_commit();\n".
                "\t\t\t\t\t\treturn true;\n".
                "\t\t\t\t\t}\n".
                "\t\t\t\t}\n".
                "\t\t\t}\n".
                "\t\t}\n";
	$output.=str_replace($variables,$substitutions,$input);
	$output.="\t}\n";
	return $output;
}

function generateClassFetchData($tableName,$className,$fields) {
	$input="        /**\n".
        "\t *  fetchData - re-fetch the data for this %{CLASSNAME} from the database.\n".
        "\t *\n".
        "\t *  @param  int  The %{ID_FIELD}.\n".
        "\t *  @return     boolean success.\n".
        "\t */\n".
        "\tfunction fetchData(\$%{ID_FIELD}) {\n".
        "\t        \$res=db_query(\"SELECT * FROM %{TABLE_NAME}\n".
        "\t                WHERE %{ID_FIELD}='$%{ID_FIELD}'\");\n".
        "\t        //        AND group_project_id='\". \$this->ProjectGroup->getID() .\"'\");\n".
        "\t        if (!\$res || db_numrows(\$res) < 1) {\n".
        "\t                \$this->setError('%{CLASSNAME}::fetchData() Invalid ID'.db_error());\n".
        "\t                return false;\n".
        "\t     }\n".
        "\t        \$this->data_array =& db_fetch_array(\$res);\n".
        "\t        db_free_result(\$res);\n".
        "\t        return true;\n".
        "\t}\n\n";

	$variables= array("%{CLASSNAME}","%{ID_FIELD}","%{TABLE_NAME}");
	$substitutions=array($className, getIdFieldFromFieldsArr($fields), $tableName );
	$output= str_replace($variables, $substitutions, $input );
	return $output;
}

function generateClassGetID($className, $fields) {
	$output="\t/**\n".
        "\t *      getID - get this ".$className." ID\n".
        "\t *\n".
        "\t *      @return int The ".getIdFieldFromFieldsArr($fields).".\n".
        "\t */\n".
        "\tfunction getID() {\n".
        "\t\treturn \$this->data_array['".getIdFieldFromFieldsArr($fields)."'];\n".
        "\t}\n\n";

	return $output;
}

function generateClassGetField($className,$fieldName,$field) {
	$input="\t/**\n".
        "\t *      %{FUNCTION_NAME} - get the field %{FIELD}.\n".
        "\t *\n".
        "\t *      @return %{TYPE} The field.\n".
        "\t */\n".
        "\tfunction %{FUNCTION_NAME}() {\n".
        "\t\treturn \$this->data_array['%{FIELD}'];\n".
        "\t}\n\n";
	$functionName="get". capitalize($fieldName);
	$type="string"; // TO DO: Check the Field
	$variables = array("%{FUNCTION_NAME}","%{FIELD}","%{TYPE}");
	$substitutions= array($functionName, $fieldName, $field["type"] );
	$output = str_replace($variables, $substitutions, $input );

	return $output;
}

function generateClassUpdate($tableName,$className,$fields) {
        /* Comments */
        $output="\t/**\n".
                "\t * update - update a ".$className." in the database.\n\t *\n";
        foreach ($fields as $fieldName => $field ) {
                $output.="\t *\t@param  ".$field["type"]." ".$fieldName.".\n";
        }
        $output.="\t * @return boolean Success.\n\t */\n";
        /* Declaration */
        $output.="\tfunction update(";
        $count = 0;
        $lineLength = strlen("    function update(");
        foreach ($fields as $fieldName => $field) {
                if($count++ != 0)
                        $output.=",";  // First occurence hasn't a comma before
                $lineLength += strlen($fieldName );
                if ($lineLength >= 80 ) {
                        $output.="\n\t\t"; // New line at 80
                        $lineLength= 8;
                }
                $output.="\$".$fieldName;
        }
        $output.=") {\n";
        /* Validations */
        $output.="\t\t\$v = new Validator();\n";
        foreach ($fields as $fieldName => $field ) {
                $output.="\t\t\$v->check(\$".$fieldName.", \"".$fieldName."\");\n";
        }
	$output.="\t\tif (!\$v->isClean()) {\n".
                 "\t\t\t\$this->setError(\$v->formErrorMsg(\"Must include \"));\n".
                 "\t\t\treturn false;\n".
                "\t\t}\n";

        /* Perms Checks */
        $output.="\t\t/*\n".
				"\t\t//  CHECK FOR PERMISSION\n".
                "\t\t\$perm =& \$this->ProjectGroup->Group->getPermission( session_get_user() );\n\n".
                "\t\tif (!\$perm || !is_object(\$perm)) {\n".
				"\t\t} elseif (\$perm->isError()) {\n".
                "\t\t\t\$this->setPermissionDeniedError();\n".
                "\t\t\treturn false;\n".
				"\t\t} elseif (!\$perm->isXXXAdmin()) {\n".
                "\t\t\t\$this->setPermissionDeniedError();\n".
                "\t\t\treturn false;\n".
                "\t\t}*/\n";

        /* Hard Work: SQL Sentence */
        $output.="\t\tdb_begin();\n\n";

	/* Checks */
	$output.="\t\t/* Several Checks */\n";
	$output.="\t\t/* You _SHOULD_ check compulsory fields here */\n";

	$output.="\t\t\$sql=\"UPDATE ".$tableName." SET \n\t\t\t";
	$count = 0;
	foreach($fields as $fieldName => $field ) {
		if($count++ !=0 ) {
			$output.=",\n";
		}
		if ($field["type"]=="string" || $field["type"] =="bpchar") {
	                $output.="\t\t\t".$fieldName."='\".";
			$output.="htmlspecialchars(\$".$fieldName.").\"'";
		} else {
	                $output.="\t\t\t".$fieldName."='";
			$output.="\$".$fieldName."'";
		}
	}
        $output .= "\n\t\t\tWHERE ".getIdFieldFromFieldsArr($fields)."='\".\$this->getID().\"'\";\n";
	$output .= "\t\t\t// WHERE group_project_id='$group_project_id'\n".
                   "\t\t\t//         AND ".getIdFieldFromFieldsArr($fields)."='\".\$this->getID().\"'\";\n";

	$output .= "\t\t\$res=db_query(\$sql);\n".
                   "\t\tif (!\$res || db_affected_rows(\$res) < 1) {\n".
                   "\t\t\t\$this->setError('Error On Update: '.db_error());\n".
                   "\t\t\tdb_rollback();\n".
                   "\t\t\treturn false;\n".
                   "\t\t} else {\n".
                   "\t\t\tif (!$this->fetchData(\$this->getID())) {\n".
                   "\t\t\t\t\$this->setError('Error On Update: '.db_error());\n".
                   "\t\t\t\tdb_rollback();\n".
                   "\t\t\t\treturn false;\n".
                   "\t\t\t} else {\n".
                   "\t\t\t\t/* \$this->sendNotice(); */\n".
                   "\t\t\t\tdb_commit();\n".
                   "\t\t\t\treturn true;\n".
                   "\t\t\t}\n".
                   "\t\t}\n".
		   "\t}\n";

	return $output;
}

function generateClassDelete($tableName, $fields) {
	$output .= '
	/**
	 *	delete() - delete this row from the database.
	 *
	 *	@param	boolean	I\'m Sure.
	 *	@return	boolean	true/false.
	 */
	function delete($sure) {
		if (!$sure) {
			$this->setError(\'Must be sure before deleting\');
			return false;
		}
		$perm =& $this->Group->getPermission( session_get_user() );
		if (!$perm || !is_object($perm)) {
			$this->setPermissionDeniedError();
			return false;
		} elseif ($perm->isError()) {
			$this->setPermissionDeniedError();
			return false;
		} elseif (!$perm->isAdmin()) {
			$this->setPermissionDeniedError();
			return false;
		} else {
			$res=db_query("DELETE FROM '.$tableName.' WHERE
				'.getIdFieldFromFieldsArr($fields).'=\'".$this->getID()."\'");
			if (!$res || db_affected_rows($res) < 1) {
				$this->setError(\'Could Not Delete: \'.db_error());
			} else {
				return true;
			}
		}
	}';
	return $output;
}

function generateClassBottom($className) {
	return "\n}\n\n?>";
}

function generateClass($tableName, $fields ) {
	$className = capitalize($tableName);
	$output=generateClassHead($className); // Done
	$output.=generateClassVars($className); // Done
	$output.=generateClassConstructor($className,$fields); // Done
	$output.=generateClassObjectCreator($tableName,$className,$fields); // Done
	$output.=generateClassFetchData($tableName, $className,$fields); // Done
	$output.=generateClassGetID($className,$fields); // DONE
	foreach($fields as $fieldName => $field) {
		$output.=generateClassGetField($className,$fieldName,$field); // Done
	}
	$output.=generateClassDelete($tableName,$fields);
	$output.=generateClassUpdate($tableName, $className,$fields); // Done
	$output.=generateClassBottom($className); // Done
	return $output;
}


global $conn;

if ( $argc < 2 ) {
	print "Usage: ".$argv[0]." <table_name>\n";
	exit -1;
}

$tableName=$argv[1];

$meta = pg_meta_data($conn, $tableName);

if ( ! is_array($meta) ) {
	print "Error: table $tableName not found\n";
	exit -1;
}
print generateHeader($tableName );
print generate_GETOBJECT($tableName );
print generateClass($tableName, $meta );

?>
