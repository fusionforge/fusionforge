#!/bin/perl

@wiki = qw(11 48 52 54 55 56 58 59 5 61 64 65 70 71 74 75 7);

# Creation du zip.
for $w (@wiki) {
	print "Zip dump of $w\n";
	system("wget http://acos.nmu.alcatel.fr/plugins/wiki/index.php?zip=all&type=g&id=$w");
}
