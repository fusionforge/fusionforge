#! /bin/sh
# 
# $Id$
#
# Configure exim for Sourceforge
# Roland Mas, debian-sf (Sourceforge for Debian)

set -e

if [ $# != 1 ] 
    then 
    exec $0 default
else
    target=$1
fi

case "$target" in
    default)
	echo "Usage: $0 {configure|purge}"
	exit 1
	;;
    configure)
	# Redirect "noreply" mail to the bit bucket (if need be)
	noreply_to_bitbucket=$(perl -e'require "/etc/sourceforge/local.pl"; print "$noreply_to_bitbucket\n";')
	if [ "$noreply_to_bitbucket" = "true" ] ; then
	    if ! grep -q "^noreply:" /etc/aliases ; then
		echo "### Next line inserted by Sourceforge install" >> /etc/aliases
		echo "noreply: /dev/null" >> /etc/aliases
	    fi
	fi

	# Redirect "sourceforge" mail to the site admin
	server_admin=$(perl -e'require "/etc/sourceforge/local.pl"; print "$server_admin\n";')
	if ! grep -q "^sourceforge:" /etc/aliases ; then
	    echo "### Next line inserted by Sourceforge install" >> /etc/aliases
	    echo "sourceforge: $server_admin" >> /etc/aliases
	fi
	[ -x /usr/bin/newaliases ] && newaliases

	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	# First, get the list of local domains right
	perl -e '
require ("/etc/sourceforge/local.pl") ;
$seen_sf_domains = 0 ;
while (($l = <>) !~ /^\s*local_domains/) {
  print $l;
  $seen_sf_domains = 1 if ($l =~ /\s*SOURCEFORGE_DOMAINS=/) ;
};
# hide pgsql_servers = "localhost/sourceforge/some_user/some_password"
print "SOURCEFORGE_DOMAINS=users.$domain_name:$sys_lists_host\n" unless $seen_sf_domains ;
chomp $l ;
$l .= ":SOURCEFORGE_DOMAINS" unless ($l =~ /^[^#]*SOURCEFORGE_DOMAINS/) ;
print "$l\n" ;
while ($l = <>) { print $l; };
' < /etc/exim/exim.conf > $tmp1
	tmp2=$(mktemp /tmp/$pattern)
	# Second, insinuate our forwarding rules in the directors section
	perl -e '
require ("/etc/sourceforge/local.pl") ;

$sf_block = "# BEGIN SOURCEFORGE BLOCK -- DO NOT EDIT #
# You may move this block around to accomodate your local needs as long as you
# keep it in the Directors Configuration section (between the second and the
# third occurences of a line containing only the word \"end\")
forward_for_sourceforge:
  domains = users.$domain_name
  driver = aliasfile
  file_transport = address_file
  query = \"ldap:///uid=\$local_part,ou=People,$sys_ldap_base_dn?x-forward-email\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists:
  domains = $sys_lists_host
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?listPostaddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_admin:
  domains = $sys_lists_host
  suffix = -owner : -admin
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?listOwnerAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_request:
  domains = $sys_lists_host
  suffix = -request
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?listRequestAddress\"
  search_type = ldap
  user = nobody
  group = nogroup
# END SOURCEFORGE BLOCK #
" ;

while (($l = <>) !~ /^\s*end\s*$/) { print $l ; };
print $l ;
while (($l = <>) !~ /^\s*end\s*$/) { print $l ; };
print $l ;
$in_sf_block = 0 ;
$sf_block_done = 0 ;
@line_buf = () ;
while (($l = <>) !~ /^\s*end\s*$/) {
  if ($l =~ /^# *DIRECTORS CONFIGURATION *#/) {
    push @line_buf, $l ;
    while ((($l = <>) =~ /^#.*#/) and ($l !~ /^# BEGIN SOURCEFORGE BLOCK -- DO NOT EDIT #/)) {
      push @line_buf, $l ;
    };
    print @line_buf ;
    @line_buf = () ;
  };
  if ($l =~ /^# BEGIN SOURCEFORGE BLOCK -- DO NOT EDIT #/) {
    $in_sf_block = 1 ;
    push @line_buf, $sf_block unless $sf_block_done ;
    $sf_block_done = 1 ;
  };
  push @line_buf, $l unless $in_sf_block ;
  $in_sf_block = 0 if ($l =~ /^# END SOURCEFORGE BLOCK #/) ;
};
push @line_buf, $l ;
print $sf_block unless $sf_block_done ;
print @line_buf ;
while ($l = <>) { print $l; };
' < $tmp1 > $tmp2
	rm $tmp1
	cat $tmp2 > /etc/exim/exim.conf
	rm $tmp2
	;;
    purge)
	pattern=$(basename $0).XXXXXX
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
' < /etc/exim/exim.conf > $tmp1
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
	cat $tmp2 > /etc/exim/exim.conf
	rm $tmp2
	;;
esac
