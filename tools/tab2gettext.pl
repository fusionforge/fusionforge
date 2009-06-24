#!/usr/bin/perl
use strict;
use strict 'refs';
use warnings;

my %tab;

sub findtxt2 {
	my $key1 = shift;
	my $key2 = shift;
	my $txt = $tab{$key1}{$key2};
	return "_(\"$txt\")";
}

sub findtxt3 {
	my $key1 = shift;
	my $key2 = shift;
	my $extra = shift;
	my $txt = $tab{$key1}{$key2};
	my $txtsave = $tab{$key1}{$key2};
	my @key = split /\$/, "$txt";
	if ( @key == 2 ){
		if ($txt =~ s/\$./\%\s/g){
			return "vsprintf(_(\"$txt\"), $extra)";
		} else {
			return "TOCHECKvsprintf(_(\"$txtsave\"), $extra)";
		}
	}
}

print "Reading alltab.txt\n-------------\n";
open(FILE, "<", "alltab.txt") or die "Can't open alltab.txt: $!";
while (<FILE>){
	if(/(.[^	]*)	(.[^	]*)	(.*)/){
		$tab{"$1"}{"$2"}="$3";
	}
}
close(FILE);


print "Reading $ARGV[0]\n-------------\n";
open(FILE, "<", $ARGV[0]) or die "Can't open $ARGV[0]: $!";
binmode FILE;
my ($buf, $data, $n); while (($n = read FILE, $data, 1000000) != 0) { print "$n bytes read\n"; $buf .= $data; } close(FILE); 

$buf =~ s{\QLanguage->getText\E}{GLOBALS['Language']->getText}sg;

my $re = qr{ (                    # paren group 1 (full function)
              \QGLOBALS['Language']->getText\E
               (                  # paren group 2 (parens)
                \(
                   (              # paren group 3 (contents of parens)
                    (?:
                    (?> [^()]+ )  # Non-parens without backtracking
                       |
                    (?2)          # Recurse to start of paren group 2
                    )*
                   )
                \)
               )
              )
           }x;

my (@key,$instr,$outstr,$extra,$cnt);
while ($buf =~ /$re/g) {
	$instr=$1;
	$extra=$2;
	@key = split /,\s*/, "$3";
	$cnt = @key;
	if ( $cnt < 2 ){
		print "=($cnt)= $instr ==> FUNC ERROR (too few args ) === \n";
	} else {
		if ( $cnt == 2 ) {
			$key[0] =~ s/\'//g;
			$key[1] =~ s/\'//g;
			$outstr=findtxt2($key[0],$key[1]);
			print "=($cnt)= $instr ==> $outstr === \n";
			$buf =~ s{\$\Q$instr\E}{$outstr}s;
		} else {
			if ( $cnt == 3 ) {
				$key[0] =~ s/\'//g;
				$key[1] =~ s/\'//g;
				$outstr=findtxt3($key[0],$key[1],$key[2]);
				print "=($cnt)= $instr ==> $outstr === \n";
				$buf =~ s{\$\Q$instr\E}{$outstr}s;
			} else {
				if ( $cnt > 3 ) {
					print "=($cnt)= $instr ==> FUNC ERROR (too many args) === \n";
				}
			}
		}
	}
}

print "$buf\n";

#$buf =~ /$re/;
#print "\$1 = $1\n", "\$2 = $2\n";
#my @key = split /,\s*/, "$2";

#$key[0] =~ s/\'//g;
#$key[1] =~ s/\'//g;

#print "$key[0]\n";
#print "$key[1]\n";

#my @file  = <FILE>;
#print "@file";
