<?php
// GForge Universal Site Modifications
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
// GForge hostnames
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
// The default GForge domain
// this is used where ever the "naked" form of the GForge domain
// might be used.  E.g., "mailto:admin@gforge.net"
$sys_default_domain = 'localhost';
$sys_fallback_domain = 'gforge2.company.com';
$sys_internal_network = '192.168.1.';
 
// Machine that hosts CVS
$sys_cvs_host = 'cvs.company.com';
 
// Machine used for downloading sources/packages
$sys_download_host = "download.company.com";
 
// Machine used for uploading sources/packages
$sys_upload_host = "upload.company.com";

// Machine(s) that host users' shell accounts
//  N.B. to the SourceForge Crew: What's the difference between the user
// host and the shell host?  They are clearly two different hostnames
// in the source code, but they seem to serve the same purpose..?
$sys_shell_host = "shell.company.com";
$sys_users_host = "users.company.com";
$homedir_prefix='/home';
$groupdir_prefix='/home/groups';
 
// Machine that hosts docs (such as the FAQs and the various software
// licenses (*BSD, [L]GPL, etc.).  You REALLY want this to be the same
// machine that the SourceForge code is running on because all of the
// PHP makes reference to these documents in terms of relative paths that
// are part of the GForge code tree.
$sys_docs_host = "sfdocs.company.com";
 
// Machine that hosts the GForge mailing lists (This could also be
// the mail host if you have enough horsepower & bandwidth)
$sys_lists_host = "lists.company.com";
 
// Domain Name Servers
//    N.B.: Use terminated FQDNs here (with the final ".") so the resolver
//    doesn't attempt to recurse in the case of a slightly broken DNS
//    configuration
$sys_dns1_host = "ns1.company.com";
$sys_dns2_host = "ns2.company.com";

//Databases, html/php/other paths
//server to use for updates and reads
$sys_dbhost="localhost";
//whether or not to use replication
$sys_db_use_replication=false;
$sys_dbreadhost='localhost';
$sys_dbreaddb='stats';
$sys_dbname="gforge";
$sys_dbuser="gforge";
$sys_dbpasswd="gforge";
$sys_server="pgsql";

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


//
//	Jabber Configuration
//
$sys_use_jabber=0;
	//messages from the system will be sent to this address
$sys_jabber_server='jabber.company.com';
$sys_jabber_port='5222';
	// messages sent to jabber accounts will come from this user
	// It is similar to the "From: noreply@gforge.org" used in emails
$sys_jabber_user='noreply';
$sys_jabber_pass='temp123';

//
//	File Upload Configuration
//
//	Create a directory, which is writable by your webserver, but not
//	within its document root (does not fall under www/ in the tarball)
//	Your php.ini file may have to be modified to allow writing outside
//	the webserver's directory
//
$sys_upload_dir='/var/www/gforge-files/';

// Where the GForge files are placed
// *** IMPORTANT: sys_urlroot *MUST* be an ABSOLUTE FILEYSTEM PATH NAME
//             that points to the www directory of the GForge
//             installation.  If you use ANY form of relative path
//             you will break the html_image function in include/html.php
//
$sys_urlroot="/var/www/gforge/www/";
 
// Name of the system as a whole (needed by various utils and titles)
$sys_name="devportal";

// session cookie settings
//
//	IMPORTANT - YOU MUST CHANGE "foobar" to a long, random number
//
$sys_session_key        = "foobar";
$sys_session_expire     = 60 * 60 * 24 * 7; 

// Require that user give unique (not yet existent in db) email upon
// registration
$sys_require_unique_email=0;
 
// Themeing related vars... Some of this needs to change in the session stuff
// The theme base directory, everything else is handled by theme_sysinit()
$sys_themeroot=$sys_urlroot."themes/";
// If you want an other default theme or language
$sys_theme='dialogos3';
#$sys_lang='English';
$sys_lang='Italian';

// Akamization of images
// example: http://images.company.com
$sys_images_url = "";
$sys_images_secure_url = "";

//  Groups
//  The GForge permission model is based on groups
//  certain parts of the site, like news, stats, etc
//  are based on special group_id numbers
//  group_id #1 is the super-user group of sitewide admins
$sys_news_group=3;
$sys_stats_group=2;
$sys_peer_rating_group=4;
$default_trove_cat=18;

//	JPGRAPH Package
$sys_path_to_jpgraph='/path/to/jpgraph/src/';
$sys_show_source=0;

// End of customizations -- place nothing after the closing PHP tag!
?>
