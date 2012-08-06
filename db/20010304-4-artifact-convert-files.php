#! /usr/bin/php
<?php

require_once (dirname(__FILE__)).'/../www/env.inc.php';
require_once $gfcommon.'include/pre.php';

db_begin();

$rel = db_query_params ('SELECT id,bin_data FROM artifact_file ORDER BY id ASC',
						      array ());
echo db_error();

$rows=db_numrows($rel);

for ($i=0; $i<$rows; $i++) {
	$res = db_query_params ('UPDATE artifact_file SET bin_data=$1 WHERE id=$2',
				array (base64_encode(util_unconvert_htmlspecialchars(db_result($rel,$i,'bin_data'))),
				       db_result($rel,$i,'id'))) ;

	echo db_error();
	echo "<br />Num: $i | id: ".db_result($rel,$i,'id');

}

db_commit();
if (db_error()) {
	echo db_error()."\n";
} else {
	echo "SUCCESS\n";
}
db_query_params ('vacuum analyze;',
			array()) ;


// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
