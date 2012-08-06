<?php // $Id: pcre-crash.php 7181 2009-10-05 14:25:48Z vargenau $
	$blockpats=array();

        $blockpats[] = '[ \t]+\S(?:.*\s*\n[ \t]+\S)*';
        // Tables
        $blockpats[] = '\|(?:.*\n\|)*';

        // List items
        $blockpats[] = '[#*;]*(?:[*#]|;.*?:)';

        // Footnote definitions
        $blockpats[] = '\[\s*(\d+)\s*\]';

        if (0 and !$debug_skip) {
        // Plugins
        $blockpats[] = '<\?plugin(?:-form)?\b.*\?>\s*$';
        }

        // Section Title
        $blockpats[] = '!{1,3}[^!]';

        $block_re = ( '/\A((?:.|\n)*?)(^(?:'
                      . join("|", $blockpats)
                      . ').*$)\n?/m' );


$pat = "/\A(
  (?:.|\n)*?)
  (^ (?:[ \t]+\S
       (?:.*\s*\n[ \t]+\S)* |
       \|(?:.*\n\|)* | [#*;]*(?:[*#]|;.*?:) | 
       \[\s*(\d+)\s*\] |
       <\?plugin(?:-form)?\b.*\?>\s*$ |
       !{1,3}[^!])
  .*$)\n?/Axm";

//     /\A((?:.|\n)*?)(^(?:[ \t]+\S(?:.*\s*\n[ \t]+\S)*|\|(?:.*\n\|)*|[#*;]*(?:[*#]|;.*?:)|\[\s*(\d+)\s*\]|!{1,3}[^!]).*$)\n?/m

// cli works fine, but sapi (Apache/2.0.48 or Apache 1) crashes.
$subj = str_repeat("123456789 ", 200);
preg_match($pat, $subj, $m);
//preg_match($block_re, $subj, $m);
echo "ok\n";

?>
