#!/usr/bin/perl 
#
# Thanks to Paul D.Smith On offsite talk
# 
# $Id$ 
# 
use DBI; 

require("../include.pl"); # Include all the predefined functions 

&db_connect; 

$server_ip = '12.34.56.78'; 

# Read the template HTTP zone file 

@httpd_zone = open_array_file("httpd.zone"); 

# Get group information from the DB 
# 
my $query = "SELECT http_domain,unix_group_name,group_name FROM groups WHERE http_domain LIKE
'%.%' AND status = 'A'"; 
my $c = $dbh->prepare($query); 
$c->execute(); 
while (my ($http_domain,$unix_group_name,$group_name) = $c->fetchrow()) { 

push @httpd_zone, <<EOF; 

### Host entries for $group_name 

<Directory "$grpdir_prefix$unix_group_name/htdocs"> 
AllowOverride AuthConfig FileInfo 
Options Indexes Includes 
Order allow,deny 
Allow from all 
</Directory> 
<Directory "$grpdir_prefix$unix_group_name/cgi-bin"> 
AllowOverride AuthConfig FileInfo 
Options ExecCGI 
Order allow,deny 
Allow from all 
</Directory> 
<VirtualHost $server_ip> 
Servername $http_domain 
DocumentRoot "$grpdir_prefix$unix_group_name/htdocs/" 
CustomLog $grpdir_prefix$unix_group_name/log/combined_log combined 
ScriptAlias /cgi-bin/ "$grpdir_prefix$unix_group_name/cgi-bin/" 
</VirtualHost> 
EOF 
} 

write_array_file("$file_dir/httpd.zone", @httpd_zone); 
