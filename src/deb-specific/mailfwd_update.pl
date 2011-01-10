#!/usr/bin/perl
#
# mailfwd_update.pl - Script to create ~/.forward for each user
# based on ssh_dump_update.pl
# changes Copyright © 2010
#	Thorsten Glaser <t.glaser@tarent.de>
# Licence: GPLv2+ (FusionForge)

use DBI;
use English;

## Become this effective user (EUID/EGID) and perform this action.
##
## This protect against symlink attacks; they are inevitable when
## working in a directory owned by a local user.  We could naively
## check for the presence of symlinks, but then we'd still be
## vulnerable to a symlink race attack.
##
## We'll use set_e_uid/set_e_gid for efficiency and simplicity
## (e.g. we can get the return value directly), which is enough for
## opening files and similar basic operations.  When calling external
## programs, you should use fork&exec&setuid/setgid.
##
# arg1: username
# arg2: a Perl sub{}
sub SudoEffectiveUser {
    my $user = $_[0];
    my $sub_unprivileged = $_[1];

    my ($uid,$gid) = GetUserUidGid($user);
    if ($uid eq "" or $gid eq "") {
	print "Unknown user: $user";
	return;
    }

    my $old_GID = $GID; # save additional groups
    $! = '';
    $EGID = "$gid $gid"; # set egid and additional groups
    if ($! ne '') {
	warn "Cannot setegid($gid $gid): $!";
	return;
    }
    $EUID = $uid;
    if ($! ne '') {
	warn "Cannot seteuid($uid): $!";
	return;
    }

    # Perform the action under this effective user:
    my $ret = &$sub_unprivileged();

    # Back to root
    undef($EUID);     # restore euid==uid
    $EGID = $old_GID; # restore egid==gid + additional groups

    return $ret;
}

## Get system uid/gid
sub GetUserUidGid {
    my $user = $_[0];
    my ($name,$passwd,$uid,$gid,
	$quota,$comment,$gcos,$dir,$shell,$expire) = getpwnam($user);
    return ($uid,$gid);
}

# Run as gforge
my($name,$passwd,$uid,$gid,$quota,$comment,$gcos,$dir,$shell) = getpwnam("gforge");
$> = $uid;

require("/usr/share/gforge/lib/include.pl");  # Include all the predefined functions

my $verbose=0;
my $fwd_array = ();

&db_connect;

$dbh->{AutoCommit} = 0;

# Dump the Table information
$query = "SELECT user_name,unix_uid,email FROM users WHERE email != '' AND status !='D'";
$c = $dbh->prepare($query);
$c->execute();
while(my ($username, $unix_uid, $mailadr) = $c->fetchrow()) {
	$new_list = "$username:$unix_uid:$mailadr\n";
	push @fwd_array, $new_list;
}

# Run as root
$> = 0;

my $username;

if($verbose){print("\n\n	Processing Users fwd creation\n\n")};
while ($ln = pop(@fwd_array)) {
	($username, $uid, $mailadr) = split(":", $ln);

	$username =~ tr/[A-Z]/[a-z]/;
	$uid += $uid_add;

	push @user_authorized_keys, $mailadr . "\n";

	if($verbose){print ("Processing $username\n")};

	if (-d "$homedir_prefix/$username"){
		if($verbose){print("Writing fwd for $username: ")};

		SudoEffectiveUser($username, sub {
		    if (write_array_file("$homedir_prefix/$username/.forward", @user_authorized_keys)) {
			warn "Problem writing fwd for $username\n";
			next;
		    }
				  });

		chown $uid, $uid, ("$homedir_prefix/$username/.forward");
		chmod 0644, "$homedir_prefix/$username/.forward";

		if($verbose){print ("Done\n")};
	} else {
		if($verbose){print ("Not yet done, waiting for home creation\n")};
	}

	undef @user_authorized_keys;
}
undef @fwd_array;

### Phase 2: remove the files when needed

# Dump the Table information
$query = "SELECT user_name,unix_uid FROM users WHERE email = '' OR email IS NULL OR status = 'D'";
$c = $dbh->prepare($query);
$c->execute();
while(my ($username, $unix_uid) = $c->fetchrow()) {
	$new_list = "$username:$unix_uid\n";
	push @fwd_array, $new_list;
}

if($verbose){print("\n\n	Processing Users fwd deletion\n\n")};
while ($ln = pop(@fwd_array)) {
	($username, $uid) = split(":", $ln);

	$username =~ tr/[A-Z]/[a-z]/;
	$uid += $uid_add;

	if($verbose){print ("Processing $username\n")};

	unlink("$homedir_prefix/$username/.forward");

	if($verbose){print ("Done\n")};
}
