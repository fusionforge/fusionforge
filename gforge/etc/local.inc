<?php
// SourceForge Universal Site Modifications
// by David HM Spector <spector@zeitgeist.com>
//
// Really Important Safety Tip: --> DO NOT LEAVE ANY WHITE
// SPACE AFTER THE CLOSING PHP TAG AT THE END OF THIS FILE!
//
// Doing so will really confuse the software and cause
// 1) cookies to fail and 2) HTML page headers to fail
// which will give you some preally hard-to-debug problems.
// Why? PHP is a *pre-processor* -- anything that's not PHP gets
// emitted as part of the HTML stream and processed by the browser,
// so white space is meaningful!
//
//
// PART I - SourceForge hostnames
//
// Hostnames should be fully qualified domain names (FQDNs); using short names
// would be prettier but would stop you from distributing your SourceForge
// implementation across multiple domains.
//
// Of course, if you have a lot of machines serving a particular purpose
// such as FTP or for shell accounts, the "hostname" here might be in
// reality an addr_list of machines that is serviced by a round-robin
// mechanism or something fancy like a local-director.
//
// The default SourceForge domain
// this is used where ever the "naked" form of the SourceForge domain
// might be used.  E.g., "mailto:admin@sourceforge.net"
$sys_default_domain = 'webdev.company.com';
$sys_fallback_domain = 'sf-www.sourceforge.net';
$sys_internal_network = '192.168.1.';
 
// Machine that hosts CVS
$sys_cvs_host = 'cvs.company.com';
 
// Machine used for downloading sources/packages
$sys_download_host = "download.company.com";
 
// Machine(s) that host users' shell accounts
//  N.B. to the SourceForge Crew: What's the difference between the user
// host and the shell host?  They are clearly two different hostnames
// in the source code, but they seem to serve the same purpose..?
$sys_shell_host = "shell.company.com";
$sys_users_host = "users.company.com";
 
// Machine that hosts docs (such as the FAQs and the various software
// licenses (*BSD, [L]GPL, etc.).  You REALLY want this to be the same
// machine that the SourceForge code is running on because all of the
// PHP makes reference to these documents in terms of relative paths that
// are part of the SourceForge code tree.
$sys_docs_host = "sfdocs.company.com";
 
// Machine that hosts the SourceForge mailing lists (This could also be
// the mail host if you have enough horsepower & bandwidth)
$sys_lists_host = "lists.company.com";
 
// Domain Name Servers
//    N.B.: Use terminated FQDNs here (with the final ".") so the resolver
//    doesn't attempt to recurse in the case of a slightly broken DNS
//    configuration
$sys_dns1_host = "ns1.company.com";
$sys_dns2_host = "ns2.company.com";

// Part II - Databases, html/php/other paths
//server to use for updates and reads
$sys_dbhost="localhost";
//whether or not to use replication
$sys_db_use_replication=true;
$sys_dbreadhost='localhost';
$sys_dbreaddb='stats';
$sys_dbname="alexandria";
$sys_dbuser="www";
$sys_dbpasswd="";
$sys_server="mysql";

// 
// LDAP configurataion
//
// disable ldap use altogether
$sys_use_ldap=0;
$sys_ldap_host="ldap.company.com";
$sys_ldap_port=389;
// this is dn under which all information stored
$sys_ldap_base_dn="dc=company,dc=com";
// and this, how we do access it (add permission required) 
$sys_ldap_bind_dn="cn=SF_robot,dc=company,dc=com";
$sys_ldap_passwd="";
// admin dn - login dn which has permissions to delete entries
// NOT used by web code, only by support utilities
// note that password NOT stored here
$sys_ldap_admin_dn="cn=Manager,dc=company,dc=com";

// Where files are placed when uploaded
$FTPINCOMING_DIR = "/nfs/remission/u7/ftp/incoming";

// Where the released files are located
$FTPFILES_DIR = "/nfs/garbage/sourceforge";
 
// Where the SourceForge files are placed
// *** IMPORTANT: sys_urlroot *MUST* be an ABSOLUTE FILEYSTEM PATH NAME
//             that points to the www directory of the SourceForge
//             installation.  If you use ANY form of relative path
//             you will break the html_image function in include/html.php
//
$sys_urlroot="/usr/lib/sourceforge/www/";
 
// Cache location -- this is needed by include/cache.php
// This directory must be world reachable, but writable only by the web-server
$sf_cache_dir = "/var/cache/sourceforge";
// Name of the system as a whole (needed by various utils and titles)
$sys_name="prodigy";

// session cookie settings
//
$sys_session_key        = "foobar";
$sys_session_expire     = 60 * 60 * 24 * 7; 
$sys_session_cypher     = MCRYPT_TWOFISH;
$sys_session_cyphermode = MCRYPT_MODE_ECB;

// username cookie settings
//
$sys_username_cookie_key        = "frobaz";
$sys_username_cookie_urlspace   = ".company.com";
$sys_username_cookie_expiration = 60 * 60 * 24 * 7;
$sys_username_cookie_plaintext  = true;
$sys_username_cookie_cypher     = MCRYPT_BLOWFISH;
$sys_username_cookie_cyphermode = MCRYPT_MODE_ECB;

// Require that user give unique (not yet existent in db) email upon
// registration
$sys_require_unique_email=0;
 
// Part III - GUI modifications (menu colors, etc.)
//    See the top of the file include/html.php, this is where the menu colors
//    and colors used throughout SourceForge are defined.
 
// Themeing related vars... Some of this needs to change in the session stuff
// The theme base directory, everything else is handled by theme_sysinit()
$sys_themeroot=$sys_urlroot."themes/";

// Akamization of images
$sys_images_url = "";

// Part IV - Groups
//  The SourceForge permission model is based on groups
//  certain parts of the site, like news, stats, etc
//  are based on special group_id numbers
//  group_id #1 is the super-user group of sitewide admins
$sys_news_group=714;
$sys_stats_group=11084;

// End of customizations -- place nothing after the closing PHP tag!
?>
