#!/usr/bin/perl -w

# Taken from an example script to use DBD::Pg to listen for async notifications
# by andrew at supernews.net, but don't bother emailing me about it;
# try #PostgreSQL on irc.freenode.net but read all the relevent
# manpages first.

use DBI;

my $database_name;
my $database_user;
my $database_password;

my $forge_get_config = "/opt/gforge/utils/forge_get_config";
my $jobs_dir         = "/opt/gforge/cronjobs";

chomp($database_name = `$forge_get_config database_name`);
chomp($database_user = `$forge_get_config database_user`);
chomp($database_password = `$forge_get_config database_password`);

my $dbh = DBI->connect("dbi:Pg:dbname=$database_name", $database_user, $database_password,
		       { RaiseError => 1, AutoCommit => 1 });

$dbh->do("LISTEN create_scm_repos;");
$dbh->do("LISTEN scm_update;");

$sth=$dbh->prepare("INSERT INTO cron_history (rundate,job,output) values (?, ?, ?)");

while (1)
{
    my $notifies = $dbh->func('pg_notifies');
    if (!$notifies)
    {
		# No notifications received. So sleep waiting for data on the backend connection.
		my $fd = $dbh->func('getfd');
		my $rfds = '';
		vec($rfds,$fd,1) = 1;
		my $n = select($rfds, undef, undef, 30);

		$notifies = $dbh->func('pg_notifies');
    }
    while ($notifies)
    {
		# the result from pg_notifies is a ref to a two-element array,
		# with the notification name and the sender's backend PID.
		my ($n,$p) = @$notifies;
		$output = "Running: $jobs_dir/$n.php $p\n";
		$output .= `$jobs_dir/$n.php 2>&1`;

		$sth->execute(time(),903,$output);

		$notifies = $dbh->func('pg_notifies');
    }
}
