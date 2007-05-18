#! /bin/sh
# 
# $Id$
#
# Configure Postfix for GForge
# Julien Goodwin
# Based of install-exim.sh by: Christian Bayle, Roland Mas, debian-sf (GForge for Debian)


set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure-files)
	cp -a /etc/aliases /etc/aliases.gforge-new
	# Redirect "noreply" mail to the bit bucket (if need be)
	noreply_to_bitbucket=$(perl -e'require "/etc/gforge/local.pl"; print "$noreply_to_bitbucket\n";')
	if [ "$noreply_to_bitbucket" = "true" ] ; then
	    if ! grep -q "^noreply:" /etc/aliases.gforge-new ; then
		echo "### Next line inserted by GForge install" >> /etc/aliases.gforge-new
		echo "noreply: /dev/null" >> /etc/aliases.gforge-new
	    fi
	fi

	# Redirect "gforge" mail to the site admin
	server_admin=$(perl -e'require "/etc/gforge/local.pl"; print "$server_admin\n";')
	if ! grep -q "^gforge:" /etc/aliases.gforge-new ; then
	    echo "### Next line inserted by GForge install" >> /etc/aliases.gforge-new
	    echo "gforge: $server_admin" >> /etc/aliases.gforge-new
	fi

	cp -a /etc/postfix/main.cf /etc/postfix/main.cf.gforge-new

	perl -pi -e's/SOURCEFORGE_DOMAINS/GFORGE_DOMAINS/;' \
            -e's/BEGIN SOURCEFORGE BLOCK -- DO NOT EDIT/BEGIN GFORGE BLOCK -- DO NOT EDIT/;' \
            -e's/END SOURCEFORGE BLOCK/END GFORGE BLOCK/;' /etc/postfix/main.cf.gforge-new

	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	# First, get the list of local domains right - add gforge domains to 'mydestination'
	perl -e '
require ("/etc/gforge/local.pl") ;
my $l;
while (($l = <>) !~ /^\s*mydestination/) { print $l; };
chomp $l;
$l .= ", users.$domain_name" unless ($l =~ /^[^#]*users.$domain_name/);
$l .= ", $sys_lists_host" unless ($l =~ /^[^#]*$sys_lists_host/);
print "$l\n";
while ($l = <>) { print $l; };
' < /etc/postfix/main.cf.gforge-new > $tmp1
	tmp2=$(mktemp /tmp/$pattern)
	# Second, insinuate our forwarding rules in the directors section
	perl -e '
require ("/etc/gforge/local.pl") ;

my $gf_block;
my $l;
my $seen_gf_block;
my $seen_alias_maps;

$gf_block = qq{### BEGIN GFORGE BLOCK -- DO NOT EDIT ###
#You may move this block around to accomodate your local needs as long as you
# keep it in an appropriate position, where \"appropriate\" is defined by you.

pgsql_gforge_users_hosts = $sys_dbhost
pgsql_gforge_users_user = gforge_mta
pgsql_gforge_users_password = gforge_mta
pgsql_gforge_users_dbname = $sys_dbname
pgsql_gforge_users_domain = users.$domain_name
pgsql_gforge_users_query = SELECT email FROM mta_users WHERE login = '\''%u'\''

pgsql_gforge_lists_hosts = $sys_dbhost
pgsql_gforge_lists_user = gforge_mta
pgsql_gforge_lists_password = gforge_mta
pgsql_gforge_lists_dbname = $sys_dbname
pgsql_gforge_lists_domain = lists.$domain_name
pgsql_gforge_lists_query = SELECT post_address FROM mta_lists WHERE list_name = '\''%u'\''
pgsql_gforge_lists_result_format = "%s"

### END GFORGE BLOCK ###
};
$seen_gf_block = 0;
$seen_alias_maps = 0;
while ($l = <>) {
	if ($l =~ /^\s*virtual_alias_maps/) {
		chomp $l;
		$l .= ", pgsql:pgsql_gforge_users" unless ($l =~ /^[^#]*pgsql:pgsql_gforge_users/);
		$l .= ", pgsql:pgsql_gforge_lists" unless ($l =~ /^[^#]*pgsql:pgsql_gforge_lists/);
		print "$l\n";
		$seen_alias_maps = 1;
	} else {
		if ($l =~ /^\s*\#\#\# BEGIN GFORGE BLOCK \-\- DO NOT EDIT \#\#\#/) {
			$seen_gf_block = 1;
		} elsif ($l =~ /^### GFORGE ADDITION - The following line can be moved and this line removed ###/) {
                        # Ignore that old line
                } elsif ($l =~ /^\s*virtual_maps = pgsql:pgsql_gforge_users, pgsql:pgsql_gforge_lists$/) {
                        # Ignore that one too
		} elsif ($l =~ /^\s*virtual_maps = .*pgsql:pgsql_gforge_users, pgsql:pgsql_gforge_lists/) {
                        chomp $l ;
                        $l =~ s/, pgsql:pgsql_gforge_users// ;
                        $l =~ s/, pgsql:pgsql_gforge_lists// ;
                        $l =~ s/pgsql:pgsql_gforge_users, // ;
                        $l =~ s/pgsql:pgsql_gforge_lists, // ;
                        print "$l\n" ;
		} else {
                        print $l;
                }
	};
};

if ($seen_gf_block == 0) {
	print $gf_block;
};

if ($seen_alias_maps == 0) {
	print "### GFORGE ADDITION - The following line can be moved and this line removed ###\n";
	print "virtual_alias_maps = pgsql:pgsql_gforge_users, pgsql:pgsql_gforge_lists\n";
};
' < $tmp1 > $tmp2
	rm $tmp1
	cat $tmp2 > /etc/postfix/main.cf.gforge-new
	rm $tmp2
	;;
    
    configure)
	[ -x /usr/bin/newaliases ] && newaliases
	;;
    
    purge-files)
	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	cp -a /etc/aliases /etc/aliases.gforge-new
	# Redirect "noreply" mail to the bit bucket (if need be)
	noreply_to_bitbucket=$(perl -e'require "/etc/gforge/local.pl"; print "$noreply_to_bitbucket\n";')
	if [ "$noreply_to_bitbucket" = "true" ] ; then
	    grep -v "^noreply:" /etc/aliases.gforge-new > $tmp1
	    cat $tmp1 > /etc/aliases.gforge-new
	fi
	rm -f $tmp1

	cp -a /etc/postfix/main.cf /etc/postfix/main.cf.gforge-new

	perl -pi -e's/SOURCEFORGE_DOMAINS/GFORGE_DOMAINS/ ; s/BEGIN SOURCEFORGE BLOCK -- DO NOT EDIT/BEGIN GFORGE BLOCK -- DO NOT EDIT/ ; s/END SOURCEFORGE BLOCK/END GFORGE BLOCK/' /etc/postfix/main.cf.gforge-new

	tmp1=$(mktemp /tmp/$pattern)
	# First, replace the list of local domains
	perl -e '
require ("/etc/gforge/local.pl") ;
while (($l = <>) !~ /^\s*mydestination/) {
  print $l;
};
chomp $l ;
$l =~ /^(\s*mydestination\s*=\s*)(\S.*)/ ;
$head = $1 ;
$dests = $2 ;
$dests =~ s/, users.$domain_name// ;
$dests =~ s/, $sys_lists_host// ;
$l = $head . $dests ;
print "$l\n" ;
while ($l = <>) { print $l; };
' < /etc/postfix/main.cf.gforge-new > $tmp1
	tmp2=$(mktemp /tmp/$pattern)
	# Second, kill our forwarding rules
	perl -e '
$in_sf_block = 0 ;
while ($l = <>) {
  if ($l =~ /^### BEGIN GFORGE BLOCK -- DO NOT EDIT ###/) {
    $in_sf_block = 1 ;
  }
  print $l unless $in_sf_block ;
  $in_sf_block = 0 if ($l =~ /^### END GFORGE BLOCK ###/) ;
};
print $l ;
while ($l = <>) { print $l; };
' < $tmp1 > $tmp2
	rm $tmp1
	cat $tmp2 > /etc/postfix/main.cf.gforge-new
	rm $tmp2
	;;

    purge)
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
	;;

esac
