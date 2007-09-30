#!/usr/bin/perl
# combine the ploticus data files for all database backends
# // test	nr	file	dba	SQL	ADODB

@DB = qw/file dba SQL ADODB/;
for $db (@DB) {
  open F, "< all_$db.data" or die;
  $i=0;
  while (<F>) {
    chomp;
    if (/^(\d+)\t(.+)/) {
      $t{$i} = "\"$2\"" unless $t{$i};
      $m{$db}{$i++} = $1;
    }
  }
  close F;
  $max = $max < $i ? $i : $max;
}
open F, "> combine_db.data";
print F "// test\tinc\t",join("\t",@DB),"\n";
$i=0;
do {
  print F $t{$i};
  print F "\t",$i;
  for $db (@DB) { print F "\t",$m{$db}{$i}; }
  print F "\n";
} while ($i++ < $max);

