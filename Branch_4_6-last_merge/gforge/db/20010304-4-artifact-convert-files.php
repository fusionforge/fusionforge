#! /usr/bin/php4 -f
<?php

require ('squal_pre.php');

db_begin();

$rel = db_query("SELECT id,bin_data FROM artifact_file ORDER BY id ASC;");
echo db_error();

$rows=db_numrows($rel);

for ($i=0; $i<$rows; $i++) {

	$res=db_query("UPDATE artifact_file 
		SET bin_data='". base64_encode( util_unconvert_htmlspecialchars( db_result($rel,$i,'bin_data') ) ) ."' 
		WHERE id='". db_result($rel,$i,'id') ."'");

	echo db_error();
	echo "<br />Num: $i | id: ".db_result($rel,$i,'id');

}

db_commit();
if (db_error()) {
	echo db_error()."\n";
} else {
	echo "SUCCESS\n";
}
db_query("vacuum analyze;");

?>
