#!/usr/bin/perl

#
# Copyright (c) STMicroelectronics, 2007. All Rights Reserved.
#
# Originally written by Mohamed CHAARI, 2007
#
# This file is a part of codendi.
#
# codendi is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# codendi is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along
# with this program; if not, write to the Free Software Foundation, Inc.,
# 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.


=pod

This script aims at achieving the migration of archives, of all _active_ mailing-lists, to the ForumML database.
Only projects that enabled ForumML plugin are concerned by this migration.

=cut

my $plugins_path = `forge_get_config plugins_path`;
chomp $plugins_path;

my $config_path = `forge_get_config config_path`;
chomp $config_path;

my $source_path = `forge_get_config source_path`;
chomp $source_path;

# Search if there are lists we shouldn't treat
my $conf = "$plugins_path/forumml/etc/forumml.inc";
my %excluded_list;
if (-f $conf) {
    # Get the variable defined in forumml.inc
    my @exc_lists;
    open(FORUMML_INC, "<$conf");
    while (<FORUMML_INC>) {
	if (m/^\$forumml_excluded_lists[ ]*=[ ]*"(.*)"[ ]*;[ ]*$/) {
	    @exc_lists = split(/[ ]*,[ ]*/, $1);
	}
    }
    close(FORUMML_INC);

    # Test if given list is excluded or not
    foreach my $list (@exc_lists) {
	$excluded_list{$list} = 0;
    }
}

# Get PHP_PARAMS variable from php-laucher.sh
my $PHP_PARAMS="-q -d include_path=.:$config_path:$source_path:$source_path/www/include:$plugins_path";

#use strict;
use DBI;

require ("$source_path/lib/include.pl") ; # Include all the predefined functions 

my $dbh = DBI->connect("DBI:Pg:host=localhost ;dbname=$sys_dbname ; user= $sys_dbuser ; password=$sys_dbpasswd") or die "Couldn't connect to database: " . DBI->errstr;


# get all active mailing-lists
my $query = "SELECT list_name, group_id FROM mail_group_list WHERE status = 3";
my $req = $dbh->prepare($query);
$req->execute();
while (my ($list_name,$group_id) = $req->fetchrow()) {
    if(! exists $excluded_list{$list_name}) {
	print "Processing ".$list_name." mailing-list ...\n ";
	system("/usr/bin/php $PHP_PARAMS $plugins_path/forumml/bin/mail_2_DBFF.php $list_name 2");
    }
}
