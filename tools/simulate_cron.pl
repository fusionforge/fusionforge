#!/usr/bin/perl -w
# 
# Simple script to run all crons (for tests) 
#
# Author: aljeux <aljeux@free.fr>
#

use strict;

my $file = shift;

open(F, $file) || die "Unable to open file '$file': $!";
while (<F>) {
 next if /^\s*#/;
 next if /^\s*$/;
 if (/^([A-Z]+)="(.*)"/) {
  $ENV{$1}=$2;
  print "Loading $1=$2\n";
  next;
 }
 if (/^([A-Z]+)=(.*)/) {
  $ENV{$1}=$2;
  print "Loading $1=$2\n";
  next;
 }
 if (/\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+root\s+(.*)/) {
  print "Running $1 (as root)\n";
  system($1);
 }
 if (/\S+\s+\S+\s+\S+\s+\S+\s+\S+\s+gforge\s+(.*)/) {
  print "Running $1 (as gforge)\n";
  system('su', 'gforge', '-c', $1);
 }
}
close(F);
