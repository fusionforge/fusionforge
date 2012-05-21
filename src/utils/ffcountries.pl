#!/usr/bin/perl

use strict;
use warnings;

print "ALTER TABLE users ADD COLUMN firstname varchar(60);
ALTER TABLE users ADD COLUMN lastname varchar(60);
ALTER TABLE users ADD COLUMN address2 text;
ALTER TABLE users ADD COLUMN ccode char(2);
ALTER TABLE users ALTER COLUMN ccode SET DEFAULT 'US';
UPDATE USERS SET ccode='US',firstname=realname WHERE firstname is null;

CREATE TABLE country_code (
	country_name varchar(80),
	ccode char(2) primary key
);

";

foreach (`isoquery -c`) {
	chomp ;
	my $cc = substr($_,0,2);
	$_ =~ s/.*\t//;
	$_ =~ s/'/''/g;
	print "INSERT INTO country_code VALUES ('$_','$cc');\n";
}
