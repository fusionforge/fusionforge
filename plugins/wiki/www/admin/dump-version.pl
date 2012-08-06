#!/usr/bin/perl -l

open F, "<", "lib/prepend.php" or exit 1;
while (<F>) {
  if (/PHPWIKI_VERSION., .(.+).\);/) {
    print $1;
    exit
  }
}
close F;
