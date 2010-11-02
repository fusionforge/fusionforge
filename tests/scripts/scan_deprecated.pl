#!/bin/perl

use File::Find;

# Scan for old exit_error syntax (second argument is not a tab name)
sub scan_exit_error ($) {
	local $content = shift;

	@matches = ();
	while ($content =~ /exit_error\s*\((.*?),([^,]*?)\s*\)\s*;(.*)/s) {
		$content = $3;
		($match = $2) =~ s/\s+/ /;
		$match =~ s/^'(.*)'$/\1/;
		push(@matches, $match) if ($match !~ /^(admin|home|my|frs|tracker|docman|forums|news|mail|pm|scm|trove|surveys|summary|)$/);
	}

	$k = "exit_error>".$File::Find::name.": ";
	print $k.join("\n$k", @matches)."\n" if (@matches);
}

# Scan for getStringFromRequest used for *_id vars, should be getIntFromRequest intead.
sub scan_getStringFromRequest ($) {
	local $content = shift;

	@matches = ();
	while ($content =~ /(getStringFromRequest\s*\('[^']*?_id'\))\s*;(.*)/s) {
		$content = $3;
		push(@matches, $1);
	}

	$k = "getString>".$File::Find::name.": ";
	print $k.join("\n$k", @matches)."\n" if (@matches);	
}

# Scan for deprecated func in code.
sub scan_deprecated_func ($) {
	local $content = shift;

	@matches = ();
	while ($content =~ /(eregi?\s*\(.*?\))(.*)/) {
		$content = $2;
		push(@matches, $1);
	}

	$k = "ereg(i)>".$File::Find::name.": ";
	print $k.join("\n$k", @matches)."\n" if (@matches);
}

sub wanted {
	next unless /\.php$/;
	open(F, $_);
	$content = join('', <F>);
	close(F);

	scan_exit_error($content);
	scan_getStringFromRequest($content);
	scan_deprecated_func($content);
}

find(\&wanted, @ARGV);


