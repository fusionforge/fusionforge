<?php
//
// SourceForge: Breaking Down the Barriers to Open Source Development
// Copyright 1999-2000 (c) The SourceForge Crew
// http://sourceforge.net
//

/*
 * Export SourceForge Trove Tree in XML
 */

require ('pre.php');

header("Content-Type: text/plain");
print("<?xml version=\"1.0\"?>
<!DOCTYPE trove-tree SYSTEM \"http://$sys_default_domain/export/trove_tree_0.1.dtd\">
<trove-tree>\n");


/*
 *  This code does special formatting to achieve more human-readable look -
 *  watch out strings ends.
 */

$level=1;

print('  <category id="0" name="root" fullname="Trove Root" description="Root of the Trove tree"');

function dump_subtree($root) {
        global $level;
	$sql="SELECT * FROM trove_cat WHERE parent=$root";
        $res=db_query($sql);
        if (db_numrows($res)==0) {
		// leaf category
		print(" />\n");
        	return false;
        }
	print(">\n");
        $level++;
	while ($row = db_fetch_array($res)) {
                $indent=str_repeat(" ",$level*2);
        	print($indent.'<category id="'.$row['trove_cat_id'].'" '
                      .'name="'.$row['shortname'].'" '
                      .'fullname="'.$row['fullname'].'" '
                      .'description="'.$row['description'].'"');
        	if (dump_subtree($row['trove_cat_id'])) {
			print($indent."</category>\n");
                }
        }
        $level--;
        return true;
}


dump_subtree(0);

print("  </category>\n");
print("</trove-tree>\n");

?>
