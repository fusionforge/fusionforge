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
my $seen_virtual_maps;

$gf_block = "### BEGIN GFORGE BLOCK -- DO NOT EDIT ###
#You may move this block around to accomodate your local needs as long as you
# keep it in an appropriate position, where \"appropriate\" is defined by you.

ldap_gforge_users_server_host = $sys_ldap_host
ldap_gforge_users_server_port = 389
ldap_gforge_users_query_filter = (uid=\%s,ou=People)
ldap_gforge_users_result_attribute = debSfForwardEmail
ldap_gforge_users_search_base = $sys_ldap_base_dn
ldap_gforge_users_bind = no
ldap_gforge_users_domain = users.$domain_name

ldap_gforge_lists_server_host = $sys_ldap_host
ldap_gforge_lists_server_port = 389
ldap_gforge_lists_query_filter = (cn=\%s,ou=mailingList)
ldap_gforge_lists_result_attribute = debSfListPostAddress
ldap_gforge_lists_search_base = $sys_ldap_base_dn
ldap_gforge_lists_bind = no
ldap_gforge_lists_domain = users.$domain_name

#forward_for_gforge_lists_admin:
# domains = $sys_lists_host
# suffix = -owner : -admin
# driver = aliasfile
# pipe_transport = address_pipe
# query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debSfListOwnerAddress\"
# search_type = ldap
# user = nobody
# group = nogroup

#forward_for_gforge_lists_request:
# domains = $sys_lists_host
# suffix = -request
# driver = aliasfile
# pipe_transport = address_pipe
# query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debSfListRequestAddress\"
# search_type = ldap
# user = nobody
# group = nogroup
### END GFORGE BLOCK ###
";
$seen_gf_block = 0;
$seen_vritual_maps = 0;
while ($l = <>) {
	if ($l =~ /^\s*virtual_maps/) {
		chomp $l;
		$l .= ", ldap:ldap_gforge_users" unless ($l =~ /^[^#]*ldap:ldap_gforge_users/);
		$l .= ", ldap:ldap_gforge_lists" unless ($l =~ /^[^#]*ldap:ldap_gforge_lists/);
		print "$l\n";
		$seen_virtual_maps = 1;
	} else {
		if ($l =~ /^\s*\#\#\# BEGIN GFORGE BLOCK \-\- DO NOT EDIT \#\#\#/) {
			$seen_gf_block = 1;
		} else {
			print $l;
		};
	};
};

if ($seen_gf_block == 0) {
	print $gf_block;
};

if ($seen_virtual_maps == 0) {
	print "### GFORGE ADDITION - The following line can be moved and this line removed ###\n";
	print "virtual_maps = ldap:ldap_gforge_users, ldap:ldap_gforge_lists\n";
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

	tmp1=$(mktemp /tmp/$pattern)
	# First, replace the list of local domains
	perl -e '
while (($l = <>) !~ /^\s*local_domains/) {
  print $l unless ($l =~ /\s*SOURCEFORGE_DOMAINS=/) ;
};
chomp $l ;
$l =~ /^(\s*local_domains\s*=\s*)(\S+)/ ;
$l = $1 . join (":", grep (!/SOURCEFORGE_DOMAINS/, (split ":", $2))) ;
print "$l\n" ;
while ($l = <>) { print $l; };
' < /etc/postfix/main.cf.gforge-new > $tmp1
	tmp2=$(mktemp /tmp/$pattern)
	# Second, kill our forwarding rules
	perl -e '
while (($l = <>) !~ /^\s*end\s*$/) { print $l ; };
print $l ;
while (($l = <>) !~ /^\s*end\s*$/) { print $l ; };
print $l ;
$in_sf_block = 0 ;
while (($l = <>) !~ /^\s*end\s*$/) {
  if ($l =~ /^# BEGIN SOURCEFORGE BLOCK -- DO NOT EDIT #/) {
    $in_sf_block = 1 ;
  }
  print $l unless $in_sf_block ;
  $in_sf_block = 0 if ($l =~ /^# END SOURCEFORGE BLOCK #/) ;
};
print $l ;
while ($l = <>) { print $l; };
' < $tmp1 > $tmp2
	rm $tmp1
	cat $tmp2 > /etc/psotfix/main.cf.gforge-new
	rm $tmp2
	;;

    purge)
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
	;;

esac
