<?php

error_reporting(~E_ALL);

// fix "lib/stdlib.php:1348: Warning[2]: Compilation failed: unmatched parentheses at offset 2"

include(dirname(__FILE__)."/../lib/stdlib.php");

foreach (array(
"test", 
"test*", 
"test*.mpg", 
"Calendar/test*.mpg", 
"Calendar/*", 
"test.mpg*", 
"test\*",
"test\.*",
"test.*",
"test$",
"*test",
"*test.mpg",
"te()st",
"tes(t]",
"tes<t>",
) as $s) {
    echo '"', $s, '" => "', glob_to_pcre($s), "\"\n";
}

?>
