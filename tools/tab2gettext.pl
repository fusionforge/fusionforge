#!/usr/bin/perl
use strict;
use strict 'refs';
use strict 'vars';
use warnings;
use Getopt::Long;
use File::Find;

my %tab;
my $verbose=10;
my $write;
my $recursive;
my $filename="";

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
	my $txtsave = $txt;
	my @key = split /\$/, "$txt";
	if ( @key == 2 ){	# Text contains only one var, I assume $1
		$txt =~ s/\$./\%s/g;
		return "vsprintf(_(\"$txt\"), $extra)";
	} else {		# Text contains several vars, should be possible to deal with if array() is detected
		return "TODOBETTERvsprintf(_(\"$txtsave\"), $extra)";
	}
}

sub readalltab {
	if ($verbose > 1) {print "Reading alltab.txt\n"};
	open(FILE, "<", "alltab.txt") or die "Can't open alltab.txt: $!";
	my $re = "^(.[^	]*)	(.[^	]*)	(.*)";
	while (<FILE>){
		if(/$re/){
			$tab{"$1"}{"$2"}="$3";
		}
	}
	close(FILE);
	return %tab;
}


sub tab2gettextfile {
	my $filename = shift;
	open(FILE, "<", $filename) or die "Can't open $filename: $!";
	binmode FILE;
	my ($buf, $data, $n); while (($n = read FILE, $data, 1000000) != 0) {if($verbose > 5){print "$n bytes read\n"}; $buf .= $data; } close(FILE); 

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

	my (@key,$instr,$outstr,$extra,$params,$thrdparam,$cnt);
	while ($buf =~ /$re/g) {
		$instr=$1;
		$extra=$2;
		$params=$3;
		@key = split /,\s*/, "$params";
		$cnt = @key;
		if ( $cnt < 2 ){
			if ($verbose > 5) {print "=($cnt)= $instr ==> FUNC ERROR (too few args ) === \n"};
		} else {
			if ( $cnt == 2 ) {
				$key[0] =~ s/\'//g;
				$key[1] =~ s/\'//g;
				$outstr=findtxt2($key[0],$key[1]);
				if ($verbose > 5) {print "=($cnt)= $instr ==> $outstr === \n"};
				$buf =~ s{\$\Q$instr\E}{$outstr}s;
			} else {
				if ( $cnt == 3 ) {
					$key[0] =~ s/\'//g;
					$key[1] =~ s/\'//g;
					$thrdparam = $params;
					$thrdparam =~ s/.[^,]*,.[^,]*,//;
					$outstr=findtxt3($key[0],$key[1],$thrdparam);
					if ($verbose > 5) {print "=($cnt)= $instr ==> $outstr === \n"};
					if ($verbose > 10) {print "=(*)= thrdparam ==> $thrdparam === \n"};
					$buf =~ s{\$\Q$instr\E}{$outstr}s;
				} else {
					if ( $cnt > 3 ) {
						$key[0] =~ s/\'//g;
						$key[1] =~ s/\'//g;
						$thrdparam = $params;
						$thrdparam =~ s/.[^,]*,.[^,]*,//;
						$outstr=findtxt3($key[0],$key[1],$thrdparam);
						if ($verbose > 5) {print "=($cnt)= $instr ==> FUNC ERROR (too many args) === \n"};
						if ($verbose > 10) {print "=(*)= outstr ==> $outstr === \n"};
						if ($verbose > 10) {print "=(*)= extra ==> $extra === \n"};
						if ($verbose > 10) {print "=(*)= params ==> $params === \n"};
						if ($verbose > 10) {print "=(*)= thrdparam ==> $thrdparam === \n"};
						$buf =~ s{\$\Q$instr\E}{$outstr}s;
					}
				}
			}
		}
	}
	if ($verbose > 10) {print "$buf\n"};
	return $buf;
}

if ( ! -f "alltab.txt" ){
	system("find . -name '*.tab' | grep -v '.svn' | grep en_US | xargs cat > alltab.txt");
}

sub wanted {
	if (/^.*\.php$/s) {
		my $filename=$_;
		if ($verbose > 1) {print "Reading $File::Find::name\n";}
		my $buf=tab2gettextfile($filename);
		if ( $write ) {
			if ($verbose > 1) {print "Writing $filename\n";}
			open(FILEOUT, '>', "$filename") or die "Can't write $filename: $!";
			print FILEOUT $buf;
			close(FILEOUT);
		};
	}
}

my %h = ('verbose' => \$verbose, 'filename' => \$filename, 'write' => \$write, 'recursive' => \$recursive);
GetOptions (\%h, 'verbose=i', 'filename=s', 'write', 'recursive');

%tab = readalltab();

#if ( exists $h{filename} ) {
if ( $filename ) {
	if ($verbose > 1) {print "Reading $filename\n"};
	my $buf=tab2gettextfile($filename);
	if ( $write ) {
		if ($verbose > 1) {print "Writing $filename\n";}
		open(FILEOUT, '>', "$filename") or die "Can't write $filename: $!";
		print FILEOUT $buf;
		close(FILEOUT);
	};
}

if ( $recursive ) {
	find(\&wanted, '.');
}



