#! /usr/bin/perl -w

# Written using trial-and-error, but it ended up matching the docs I found:
# http://www.zytrax.com/books/ldap/ch6/slapd-config.html#use-schemas

@list = ();
$cur = '';

while ($l = <>) {
    chomp $l;
    next if $l =~ /^#/;
    next if $l =~ /^\s*$/;

    $l =~ s/^attributetype/olcAttributeTypes:/;
    $l =~ s/^objectclass/olcObjectClasses:/;

    if ($l =~ /^\w+:/) {
	push @list, $cur unless $cur eq "";
	$cur = $l;
    } else {
	$cur .= " $l";
    }
}
push @list, $cur if $cur ne "";


print "dn: cn=gforge,cn=schema,cn=config
objectClass: olcSchemaConfig
cn: gforge
";
map { print "$_\n" } @list;
