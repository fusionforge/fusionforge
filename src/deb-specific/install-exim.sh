#! /bin/sh
# 
# Configure Exim for GForge
# Christian Bayle, Roland Mas, debian-sf (GForge for Debian)

set -e

if [ $(id -u) != 0 ] ; then
    echo "You must be root to run this, please enter passwd"
    exec su -c "$0 $1"
fi

case "$1" in
    configure-files)
	cp -a /etc/aliases /etc/aliases.gforge-new
	# Redirect "noreply" mail to the bit bucket (if need be)
	noreply_to_bitbucket=`/usr/share/gforge/bin/forge_get_config noreply_to_bitbucket`
	if [ "$noreply_to_bitbucket" = "true" ] ; then
	    if ! grep -q "^noreply:" /etc/aliases.gforge-new ; then
		echo "### Next line inserted by GForge install" >> /etc/aliases.gforge-new
		echo "noreply: /dev/null" >> /etc/aliases.gforge-new
	    fi
	fi

	# Redirect "gforge" mail to the site admin
	server_admin=`/usr/share/gforge/bin/forge_get_config admin_email`
	if ! grep -q "^gforge:" /etc/aliases.gforge-new ; then
	    echo "### Next line inserted by GForge install" >> /etc/aliases.gforge-new
	    echo "gforge: $server_admin" >> /etc/aliases.gforge-new
	fi

	cp -a /etc/exim/exim.conf /etc/exim/exim.conf.gforge-new

	pattern=$(basename $0).XXXXXX
	tmp1=$(mktemp /tmp/$pattern)
	# First, get the list of local domains right
	perl -e '
chomp($sys_lists_host=`/usr/share/gforge/bin/forge_get_config lists_host`);
$seen_sf_domains = 0 ;
while (($l = <>) !~ /^\s*local_domains/) {
  print $l;
  $seen_sf_domains = 1 if ($l =~ /\s*SOURCEFORGE_DOMAINS=/) ;
};
# hide pgsql_servers = "localhost/gforge/some_user/some_password"
print "SOURCEFORGE_DOMAINS=$sys_users_host:$sys_lists_host\n" unless $seen_sf_domains ;
chomp $l ;
$l .= ":SOURCEFORGE_DOMAINS" unless ($l =~ /^[^#]*SOURCEFORGE_DOMAINS/) ;
print "$l\n" ;
while ($l = <>) { print $l; };
' < /etc/exim/exim.conf.gforge-new > $tmp1
	tmp2=$(mktemp /tmp/$pattern)
	# Second, insinuate our forwarding rules in the directors section
	perl -e '
chomp($sys_users_host=`/usr/share/gforge/bin/forge_get_config users_host`);
chomp($sys_lists_host=`/usr/share/gforge/bin/forge_get_config lists_host`);
chomp($sys_ldap_base_dn=`/usr/share/gforge/bin/forge_get_config ldap_base_dn`);

$sf_block = "# BEGIN SOURCEFORGE BLOCK -- DO NOT EDIT #
# You may move this block around to accomodate your local needs as long as you
# keep it in the Directors Configuration section (between the second and the
# third occurences of a line containing only the word \"end\")
forward_for_gforge:
  domains = $sys_users_host
  driver = aliasfile
  file_transport = address_file
  query = \"ldap:///uid=\$local_part,ou=People,$sys_ldap_base_dn?debGforgeForwardEmail\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_gforge_lists:
  domains = $sys_lists_host
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListPostaddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_gforge_lists_owner:
  domains = $sys_lists_host
  suffix = -owner
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListOwnerAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_gforge_lists_request:
  domains = $sys_lists_host
  suffix = -request
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListRequestAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_admin:
  domains = $sys_lists_host
  suffix = -admin
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListAdminAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_bounces:
  domains = $sys_lists_host
  suffix = -bounces
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListBouncesAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_confirm:
  domains = $sys_lists_host
  suffix = -confirm
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListConfirmAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_join:
  domains = $sys_lists_host
  suffix = -join
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListJoinAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_leave:
  domains = $sys_lists_host
  suffix = -leave
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListLeaveAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_subscribe:
  domains = $sys_lists_host
  suffix = -subscribe
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListSubscribeAddress\"
  search_type = ldap
  user = nobody
  group = nogroup

forward_for_sourceforge_lists_unsubscribe:
  domains = $sys_lists_host
  suffix = -unsubscribe
  driver = aliasfile
  pipe_transport = address_pipe
  query = \"ldap:///cn=\$local_part,ou=mailingList,$sys_ldap_base_dn?debGforgeListUnsubscribeAddress\"
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
	cat $tmp2 > /etc/exim/exim.conf.gforge-new
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
	noreply_to_bitbucket=`/usr/share/gforge/bin/forge_get_config noreply_to_bitbucket`
	if [ "$noreply_to_bitbucket" = "true" ] ; then
	    grep -v "^noreply:" /etc/aliases.gforge-new > $tmp1
	    cat $tmp1 > /etc/aliases.gforge-new
	fi
	rm -f $tmp1

	cp -a /etc/exim/exim.conf /etc/exim/exim.conf.gforge-new

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
' < /etc/exim/exim.conf.gforge-new > $tmp1
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
	cat $tmp2 > /etc/exim/exim.conf.gforge-new
	rm $tmp2
	;;

    purge)
	;;

    *)
	echo "Usage: $0 {configure|configure-files|purge|purge-files}"
	exit 1
	;;

esac
