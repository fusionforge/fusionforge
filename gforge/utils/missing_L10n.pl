#! /usr/bin/perl -w
# $Id$

# This Perl script is to help the L10n team find missing strings in a
# particular language.

# Usage:
# missing_L10n.pl <language0> <language1> ...
#
# will compare <languagen> to Base.tab and report missing entries.

use strict;
use File::Basename;

my %base;

sub build_hash($%);
sub compare_to_base(%);

# First build a hash from what Base.tab has
build_hash("../www/include/languages/Base.tab", \%base);

foreach my $lang (@ARGV)
{
    my %lang;
    build_hash("../www/include/languages/$lang.tab", \%lang);
    print "$lang:\n";
    compare_to_base(\%lang);
    print "\n";
}

################################################################

sub build_hash($%)
{
    my $file = shift;
    my $hash_ref = shift;
    open TABFILE, $file
        or die "Failed to open `$file' for reading: $!\n";
    while (<TABFILE>)
    {
        next if /^#/;
        if (/^include\s+([A-Za-z0-9]+)/)
        {
                my $dir = basename($file);
                my $included_file = "$dir/$1.tab";
                build_base_hash($included_file, $hash_ref);
        }
        elsif (/^([a-zA-Z0-9_]+)\s+([a-zA-Z0-9_]+)/)
        {
            push @{$$hash_ref{$1}}, $2;
        }
    }
    close TABFILE;
}

################################################################

sub difference(@@);

sub compare_to_base(%)
{
    my $lang_ref = shift;

    foreach my $base_category (keys %base)
    {
        my @missing_from_lang;
        my @extra_in_lang;
        if (!$$lang_ref{$base_category})
        {
            @missing_from_lang = @{$base{$base_category}};
        }
        else
        {
            my @diff;
            @diff = difference($base{$base_category},
                               $$lang_ref{$base_category});
            foreach my $diff (@diff)
            {
                if (!grep { /$diff/ } @{$$lang_ref{$base_category}})
                {
                    push @missing_from_lang, $diff;
                }
                else
                {
                    push @extra_in_lang, $diff;
                }
            }
        }
        if (@missing_from_lang || @extra_in_lang)
        {
            print "\t$base_category:\n";
            print "\t\tmissing: @missing_from_lang\n";
            print "\t\textra  : @extra_in_lang\n";
        }
    }
}

################################################################

sub difference(@@)
{
    my $a1_ref = shift;
    my $a2_ref = shift;

    my %count;
    my @diff;

    foreach my $element (@$a1_ref, @$a2_ref)
    {
        $count{$element}++;
    }
    foreach my $element (keys %count)
    {
        push @diff, $element if $count{$element} == 1;
    }
    return @diff;
}
