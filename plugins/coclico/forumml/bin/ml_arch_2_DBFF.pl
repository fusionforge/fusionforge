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
# You should have received a copy of the GNU General Public License
# along with codendi; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


=pod

This script aims at achieving the migration of archives, of all _active_ mailing-lists, to the ForumML database.
Only projects that enabled ForumML plugin are concerned by this migration.

=cut

# Search if there are lists we shouldn't treat
my $conf = '/usr/share/gforge/plugins/forumml/etc/forumml.inc';
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
my $PHP_PARAMS="-q -d include_path=.:/etc/gforge:/usr/share/gforge:/usr/share/gforge/www/include:/usr/share/gforge/plugins";

#use strict;
use DBI;

require "/etc/gforge/local.pl";
my $dbh = DBI->connect("DBI:Pg:host=localhost ;dbname=$sys_dbname ; user= $sys_dbuser ; password=$sys_dbpasswd") or die "Couldn't connect to database: " . DBI->errstr;


# get all active mailing-lists
my $query = "SELECT list_name, group_id FROM mail_group_list WHERE status = 3";
my $req = $dbh->prepare($query);
$req->execute();
while (my ($list_name,$group_id) = $req->fetchrow()) {
    if(! exists $excluded_list{$list_name}) {
	print "Processing ".$list_name." mailing-list ...\n ";
	system("/usr/bin/php $PHP_PARAMS /usr/share/gforge/plugins/forumml/bin/mail_2_DBFF.php $list_name 2");
    }
}
