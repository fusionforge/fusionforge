#! /bin/sh
#
# Configure Exim4 for GForge
# Christian Bayle, Roland Mas, debian-sf (GForge for Debian)
# Converted to Exim4 by Guillem Jover

set -e

if [ $(id -u) != 0 ] ; then
  echo "You must be root to run this, please enter passwd"
  exec su -c "$0 $1"
fi

####
# Handle the three configuration setups

cfg_exim4=/etc/exim4/exim4.conf
cfg_exim4_templ=/etc/exim4/exim4.conf.template
cfg_exim4_split_main=/etc/exim4/conf.d/main/01_exim4-config_listmacrosdefs
cfg_exim4_split_router=/etc/exim4/conf.d/router/01_gforge_forwards

cfg_exim4_main="$cfg_exim4_templ $cfg_exim4_split_main"
cfg_exim4_router="$cfg_exim4_templ"

if [ -e $cfg_exim4 ]; then
  cfg_exim4_main="$cfg_exim4_main $cfg_exim4"
  cfg_exim4_router="$cfg_exim4_router $cfg_exim4"
fi

cfg_aliases=/etc/aliases
cfg_aliases_gforge=$cfg_aliases.gforge-new

pattern=$(basename $0).XXXXXX

case "$1" in
  configure-files)
    ####
    # Configure aliases

    cp -a $cfg_aliases $cfg_aliases_gforge

    # Redirect "noreply" mail to the bit bucket (if need be)
    noreply_to_bitbucket=`/usr/share/gforge/bin/forge_get_config noreply_to_bitbucket`
    if [ "$noreply_to_bitbucket" = "true" ] ; then
      if ! grep -q "^noreply:" $cfg_aliases_gforge; then
	echo "### Next line inserted by GForge install" >> $cfg_aliases_gforge
	echo "noreply: :blackhole:" >> $cfg_aliases_gforge
      fi
    fi

    # Redirect "gforge" mail to the site admin
    server_admin=`/usr/share/gforge/bin/forge_get_config admin_email`
    if ! grep -q "^gforge:" $cfg_aliases_gforge; then
      echo "### Next line inserted by GForge install" >> $cfg_aliases_gforge
      echo "gforge: $server_admin" >> $cfg_aliases_gforge
    fi

    ####
    # Modify exim4 configurations

    # First, get the list of local domains right

    for m in $cfg_exim4_main; do
      cfg_gforge_main=$m.gforge-new
      tmp1=$(mktemp /tmp/$pattern)

      cp -a $m $cfg_gforge_main

      perl -e '
chomp($sys_dbuser=`/usr/share/gforge/bin/forge_get_config database_user`);
chomp($sys_dbname=`/usr/share/gforge/bin/forge_get_config database_name`);
chomp($sys_users_host=`/usr/share/gforge/bin/forge_get_config users_host`);
chomp($sys_lists_host=`/usr/share/gforge/bin/forge_get_config lists_host`);
$seen_gf_domains = 0;
while (($l = <>) !~ /^\s*domainlist\s*local_domains/) {
  print $l;
  $seen_gf_domains = 1 if ($l =~ /\s*GFORGE_DOMAINS=/);
  $seen_pg_servers = 1 if ($l =~ m,hide pgsql_servers = .*./var/run/postgresql/.s.PGSQL.5432..*/${sys_dbuser}_mta,);
};
print "hide pgsql_servers = (/var/run/postgresql/.s.PGSQL.5432)/mail/Debian-exim/bogus:(/var/run/postgresql/.s.PGSQL.5432)/$sys_dbname/${sys_dbuser}_mta/${sys_dbuser}_mta\n" unless $seen_pg_servers;
print "GFORGE_DOMAINS=$sys_users_host:$sys_lists_host\n" unless $seen_gf_domains;
chomp $l;
$l .= ":GFORGE_DOMAINS" unless ($l =~ /^[^#]*GFORGE_DOMAINS/);
print "$l\n" ;
while (<>) { print; };
' < $cfg_gforge_main > $tmp1

      cat $tmp1 > $cfg_gforge_main
      rm $tmp1
    done

    # Second, insinuate our forwarding rules in the directors section

    perl -e '
chomp($sys_users_host=`/usr/share/gforge/bin/forge_get_config users_host`);
chomp($sys_lists_host=`/usr/share/gforge/bin/forge_get_config lists_host`);

my $gf_block = "# BEGIN GFORGE BLOCK -- DO NOT EDIT #
# You may move this block around to accomodate your local needs as long as you
# keep it in the Directors Configuration section (between the second and the
# third occurences of a line containing only the word \"end\")

forward_for_gforge:
  domains = $sys_users_host
  driver = redirect
  file_transport = address_file
  data = \${lookup pgsql {select email from mta_users where login=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists:
  domains = $sys_lists_host
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select post_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_owner:
  domains = $sys_lists_host
  local_part_suffix = -owner
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select owner_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_request:
  domains = $sys_lists_host
  local_part_suffix = -request
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select request_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_admin:
  domains = $sys_lists_host
  local_part_suffix = -admin
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select admin_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_bounces:
  domains = $sys_lists_host
  local_part_suffix = -bounces : -bounces+*
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select bounces_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_confirm:
  domains = $sys_lists_host
  local_part_suffix = -confirm : -confirm+*
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select confirm_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_join:
  domains = $sys_lists_host
  local_part_suffix = -join
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select join_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_leave:
  domains = $sys_lists_host
  local_part_suffix = -leave
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select leave_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_subscribe:
  domains = $sys_lists_host
  local_part_suffix = -subscribe
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select subscribe_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup

forward_for_gforge_lists_unsubscribe:
  domains = $sys_lists_host
  local_part_suffix = -unsubscribe
  driver = redirect
  pipe_transport = address_pipe
  data = \${lookup pgsql {select unsubscribe_address from mta_lists where list_name=".chr(39)."\$local_part".chr(39)."}{\$value}}
  user = nobody
  group = nogroup
# END GFORGE BLOCK #
";

print $gf_block;
' > $cfg_exim4_split_router

    for r in $cfg_exim4_router; do
      echo Processing $r

      cfg_gforge_router=$r.gforge-new
      tmp1=$(mktemp /tmp/$pattern)

      cp -a $cfg_gforge_router $tmp1

      perl -e '
$routerfname = shift ;
open ROUTERS, $routerfname || die $!;
my @gf_block = <ROUTERS>;
close ROUTERS;

while (<>) {
  print;
  last if /^\s*begin\s*routers\s*$/;
};
my $in_gf_block = 0;
my $gf_block_done = 0;
my @line_buf = ();
while (<>) {
  last if /^\s*begin\s*$/;
  if (/^# BEGIN GFORGE BLOCK -- DO NOT EDIT #/) {
    $in_gf_block = 1;
    push @line_buf, @gf_block unless $gf_block_done;
    $gf_block_done = 1;
  };
  push @line_buf, $_ unless $in_gf_block;
  $in_gf_block = 0 if /^# END GFORGE BLOCK #/;
};
push @line_buf, $_;
print @gf_block unless $gf_block_done;
print @line_buf;
while (<>) { print; };
' $cfg_exim4_split_router < $tmp1 > $cfg_gforge_router

      rm $tmp1
    done

  ;;

  configure)
    [ -x /usr/bin/newaliases ] && newaliases
    invoke-rc.d exim4 restart
  ;;

  purge-files)
    tmp1=$(mktemp /tmp/$pattern)

    cp -a $cfg_aliases $cfg_aliases_gforge

    grep -v "^gforge:" $cfg_aliases_gforge > $tmp1
    # Redirect "noreply" mail to the bit bucket (if need be)
    noreply_to_bitbucket=`/usr/share/gforge/bin/forge_get_config noreply_to_bitbucket`
    if [ "$noreply_to_bitbucket" = "true" ] ; then
      grep -v "^noreply:" $tmp1 > $cfg_aliases_gforge
    else
      cat $tmp1 > $cfg_aliases_gforge
    fi

    rm -f $tmp1

    for m in $cfg_exim4_main; do
      cfg_gforge_main=$m.gforge-new
      tmp1=$(mktemp /tmp/$pattern)

      cp -a $m $tmp1

      # First, replace the list of local domains
      perl -e '
while (<>) {
  last if /^\s*domainlist\s*local_domains/;
  print unless /\s*GFORGE_DOMAINS=/;
};
chomp;
/^(\s*domainlist\s*local_domains\s*=\s*)(\S+)/;
my $l = $1 . join (":", grep(!/GFORGE_DOMAINS/, (split ":", $2)));
print "$l\n" ;
while (<>) { print; };
' < $tmp1 > $cfg_gforge_main

      rm $tmp1
    done

    if [ -f $cfg_exim4_split_router ]
    then
    	mv $cfg_exim4_split_router $cfg_exim4_split_router.gforge-new
    fi

    for r in $cfg_exim4_router; do
      cfg_gforge_router=$r.gforge-new
      tmp1=$(mktemp /tmp/$pattern)

      cp -a $cfg_gforge_router $tmp1

      # Second, kill our forwarding rules
      perl -e '
while (<>) {
  print;
  last if /^\s*begin\s*routers\s*$/;
};
my $in_gf_block = 0;
while (<>) {
  last if /^\s*begin\s*$/;
  $in_gf_block = 1 if /^# BEGIN GFORGE BLOCK -- DO NOT EDIT #/;
  print unless $in_gf_block;
  $in_gf_block = 0 if /^# END GFORGE BLOCK #/;
};
print;
while (<>) { print; };
' < $tmp1 > $cfg_gforge_router

      rm $tmp1
    done
  ;;

  purge)
  ;;

  *)
    echo "Usage: $0 {configure|configure-files|purge|purge-files}"
    exit 1
  ;;

esac

