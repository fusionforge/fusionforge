#!/usr/bin/perl
#
# **
# * Quota management support.
# *
# * Copyright 2005 (c) Sogeti-Transiciel Technologies
# *
# * @author Olivier Fourdan ofourdan@mail.transiciel.com
# * @date 2005-11-21
# *
# * This file is released under the GNU GPL license.
# *
# **

use DBI;
use Quota;

require("/usr/share/gforge/lib/include.pl");

my $BLOCK_SIZE=$ENV{'BLOCK_SIZE'} || 1024;

sub set_quota
{
    my ($gid, $group_name, $quota_soft, $quota_hard) = @_;
    my $dev = Quota::getqcarg ($grpdir_prefix);
    my ($bc,$bs,$bh,$bt, $ic,$is,$ih,$it) = Quota::query ($dev, $gid, 1);

    print  "Setting Quota for group \"$group_name\" ($gid) on device \"$dev\":\n";
    printf "  - Current usage is %i blocks (%.2f Mb).\n", $bc, $bc / $BLOCK_SIZE;
    print  "  - Current limits: $bs blocks soft, $bh blocks hard.\n";
    Quota::setqlim ($dev, $gid, $quota_soft, $quota_hard, 0, 0, 0, 1);
    Quota::sync ($dev);
    ($bc,$bs,$bh,$bt, $ic,$is,$ih,$it) = Quota::query ($dev, $gid, 1);
    print  "  - New limits: $bs blocks soft, $bh blocks hard.\n";
}

sub update_quota
{
    $sql = "SELECT unix_group_name, quota_soft, quota_hard FROM groups";
    $res = $dbh->prepare($sql);
    $res->execute();

    while ( my ($group, $quota_soft, $quota_hard) = $res->fetchrow())
    {
        my $scm_group = "scm_" . $group ;
        my $gid = getgrnam($group);
        if ($gid)
        {
            &set_quota ($gid, $group, $quota_soft, $quota_hard);
        }

        # Also set limits for the scm group, as it's a different one....
        $gid = getgrnam($scm_group);
        if ($gid)
        {
            &set_quota ($gid, $scm_group, $quota_soft, $quota_hard);
        }

    }
}

### Main ###
&db_connect;
&update_quota;
