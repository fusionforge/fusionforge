#!/usr/bin/perl
# combine the ploticus data files for all tested define combinations
# // inc mem msg =>
# // test	   nr	d1_t 	d1_f	d2_t	d2_f
#"testnowikiwords" 4	13408	13592	14880	15440
#"testwikiword"	   5	13408	13600	14880	15440 

$prefix = shift || 'all';
# @DEF should match the runme_mem tests:
#   WIKIDB_NOCACHE_MARKUP ENABLE_PAGEPERM
@DEF = qw/ USECACHE ENABLE_USER_NEW  /;
for $def (@DEF) {
  for $bool (qw/true false/) {
    $fn = sprintf("%s_%s_%s.data",$prefix,$def,$bool);
    open F, "< $fn" || break;
    $key = $def."_".$bool;
    $i=1;
    while (<F>) {
      chomp;
      if (/^\d+\t(\d+)\t(.+)/) {
	$t{$i} = "$2" unless $t{$i};
	$m{$key}{$i++} = $1;
      }
      if (m|^// |) {
	$p .= ($_."\n");
      }
    }
    $params = $p unless $params;
    close F;
    $max = $max < $i ? $i : $max;
  }
}
$fn = $prefix."_combine_define.data";
open F, "> $fn";
print F "// test      \tnr";
for $def (@DEF) { 
  print F "\t",$def," (t/f)"; }
print F "\n";

$i=0;
while (++$i < $max) {
  print F substr($t{$i},0,14);
  print F "\t",$i;
  for $def (@DEF) { 
    for $bool (qw/true false/) {
      $key = $def."_".$bool;
      $v = $m{$key}{$i} ? $m{$key}{$i} : '-1';
      print F "\t",$v; }}
  print F "\n";
}

# add the PARAMS
print F "\n";
$defs = " (".join('|',("pid",@DEF)).")=";
$defrx = qr($defs);
for (split(/\n/,$params)) {
  print F $_,"\n" unless m/$defrx/;
}
