dnl $Id$
dnl
dnl finger of blame -> Richard Offer.
dnl

# this has been developed and tested on 2.52, if it works
# on earlier versions then update this.
AC_PREREQ(2.52)

# package name, version, bug address
AC_INIT(GForge,3.0,http://gforge.org/tracker/?group_id=1)

# provide a unique file name so autoconf can check that its 
# in the right place
AC_CONFIG_SRCDIR(README.configure)


AC_COPYRIGHT([
Copyright (c) 2003 Richard Offer. All rights reserved. 
  Any local macros are licensed under the BSD license
])

AC_REVISION($Id$)



AC_PROG_AWK()
AC_CHECK_PROGS(HASHING_PROG,md5sum md5 sum)



# If you find that you are having to use too many command line options
# to get the settings you require, you should consider adding a new
# defaults TYPE, Dont forget that the definitions of the AC_ARG_WITH() macros
# will need to be changed to remove the defaults (the last parameter).


AC_ARG_ENABLE(defaults,
	AC_HELP_STRING([--enable-defaults=TYPE],
dnl we should probably display a list of valid defaults (once there are more)
   		[enable TYPE specific defaults (None)]),
	[
	case "$enableval" in

		DEBIAN|debian)
			AC_MSG_RESULT([Using Debian specific defaults (FHS)])

			homedir_prefix=/var/lib/gforge/chroot/home/users
			groupdir_prefix=/var/lib/gforge/chroot/home/groups

			sys_upload_dir=/var/lib/gforge/download/

			TOP_DIR=/usr/share/gforge
			CGIBIN_DIR=/usr/lib/gforge/cgi-bin

			sys_urlroot=/usr/share/gforge/www/
			sys_ldap_admin_dn="admin,$LDAP_BASE_DN"
			;;

		NONE|none)
			# use defaults 
			AC_MSG_RESULT([Using built-in defaults])
			;;

		/*)
			AC_MSG_RESULT([Using defaults from $enableval])
			# local defaults are stored in a file, source it.
			. $enableval
			;;

		*/*)
			AC_MSG_ERROR(["$enableval" should be an absolute path])
			;;

		*)
			AC_MSG_ERROR(["$enableval" not a valid setting for defaults])
			;;
	esac
])


# This should really be example.com, see RFC2606.
AC_ARG_WITH(domain-name,
	AC_HELP_STRING([--with-domain-name],
   		[the default domain (company.com)]),
	[domain_name="$withval"])

# defaults  for when no defaults are specified (or defaults=NONE.

this_dir=`dirname $0`
test -n "${TOP_DIR}" || TOP_DIR=`(cd $this_dir/../../ ; pwd)`

test -n "${domain_name}" || domain_name="company.com" 
test -n "${sys_default_domain}" || sys_default_domain="gforge.$domain_name" 
test -n "${sys_fallback_domain}" || sys_fallback_domain="gforge2.$domain_name" 
test -n "${sys_internal_network}" || sys_internal_network="192.168.1." 

test -n "${sys_cvs_host}" || sys_cvs_host="cvs.$domain_name" 
test -n "${sys_cvs_single_host}" || sys_cvs_single_host=1 
test -n "${sys_download_host}" || sys_download_host="download.$domain_name" 
test -n "${sys_upload_host}" || sys_upload_host="upload.$domain_name" 
test -n "${sys_shell_host}" || sys_shell_host="shell.$domain_name" 
test -n "${sys_users_host}" || sys_users_host="users.$domain_name" 
test -n "${sys_docs_host}" || sys_docs_host="sfdocs.$domain_name" 
test -n "${sys_lists_host}" || sys_lists_host="lists.$domain_name" 
test -n "${sys_dns1_host}" || sys_dns1_host="ns1.$domain_name" 
test -n "${sys_dns2_host}" || sys_dns2_host="ns2.$domain_name" 
test -n "${sys_dbhost}" || sys_dbhost="localhost" 
test -n "${sys_dbreadhost}" || sys_dbreadhost="localhost" 
test -n "${sys_dbreaddb}" || sys_dbreaddb="stats" 
test -n "${sys_dbname}" || sys_dbname="alexandria" 
test -n "${sys_dbuser}" || sys_dbuser="www" 
test -n "${sys_dbpasswd}" || sys_dbpasswd="" 

test -n "${sys_use_ldap}" || sys_use_ldap=0 
test -n "${sys_ldap_host}" || sys_ldap_host="ldap.$domain_name" 
test -n "${sys_ldap_base_dn}" || sys_ldap_base_dn="`echo $domain_name | awk -F. '{printf(\"dc=%s,dc=%s\",$(NF-1), $NF) }'`"
test -n "${sys_ldap_bind_dn}" || sys_ldap_bind_dn="cn=SF_robot,$sys_ldap_base_dn"
test -n "${sys_ldap_admin_dn}" || sys_ldap_admin_dn="cn=Manager,$sys_ldap_base_dn"
test -n "${sys_ldap_passwd}" || sys_ldap_passwd=""

test -n "${sys_use_jabber}" || sys_use_jabber=0
test -n "${sys_jabber_host}" || sys_jabber_host="jabber.$domain_name" 
test -n "${sys_jabber_user}" || sys_jabber_user="noreply" 
test -n "${sys_jabber_pass}" || sys_jabber_pass="temp123" 

test -n "${sys_path_to_jpgraph}" || sys_path_to_jpgraph="/path/to/jpgraph/src/"
test -n "${sys_upload_dir}" || sys_upload_dir=$TOP_DIR/uploads
test -n "${homedir_prefix}" || homedir_prefix="/home"
test -n "${groupdir_prefix}" || groupdir_prefix="/home/groups"

test -n "${sys_urlroot}" || sys_urlroot=$TOP_DIR/www/

test -n "${sys_name}" || sys_name="prodigy"

test -n "${sys_theme}" || sys_theme="gforge"
test -n "${sys_lang}" || sys_lang="English"

test -n "${sys_news_group}" || sys_news_group=3
test -n "${sys_stats_group}" || sys_stats_group=2
test -n "${sys_peer_rating_group}" || sys_peer_rating_group=4
test -n "${default_trove_cat}" || default_trove_cat=18

# currently unused
test -n "${sys_server}" || sys_server="pgsql"
test -n "${sys_scm}" || sys_scm="CVS"

#############################################################################
# Basic hosts configuration.
#############################################################################

AC_ARG_WITH(sys-default-domain,
	AC_HELP_STRING([--with-sys-default-domain],
		[the gforge server (gforge.DOMAIN-NAME)]),
	[sys_default_domain="$withval"])

AC_ARG_WITH(sys-fallback-domain,
	AC_HELP_STRING([--with-sys-fallback-domain],
		[the gforge fallback server (gforge2.DOMAIN-NAME)]),
	[sys_fallback_domain="$withval"])

AC_ARG_WITH(sys-internal-network,
	AC_HELP_STRING([--with-sys-internal-network],
		[netmask of internal network (192.168.1.)]),
	[sys_internal_network="$withval"])

AC_ARG_WITH(sys-cvs-host,
	AC_HELP_STRING([--with-sys-cvs-host],
		[the CVS server (cvs.DOMAIN-NAME)]),
	[sys_cvs_host="$withval"])

AC_ARG_WITH(project-cvs-hosts,
	AC_HELP_STRING([--with-project-cvs-hosts],
		[run an individual CVS server per project (a single CVS server 
for the entire site)]),
	[sys_cvs_single_host=0])

AC_ARG_WITH(sys-download-host,
	AC_HELP_STRING([--with-sys-download-host],
		[the download server (download.DOMAIN-NAME)]),
	[sys_download_host="$withval"])

AC_ARG_WITH(sys-upload-host,
	AC_HELP_STRING([--with-sys-upload-host],
		[the upload server (upload.DOMAIN-NAME)]),
	[sys_upload_host="$withval"])

AC_ARG_WITH(sys-shell-host,
	AC_HELP_STRING([--with-sys-shell-host],       
		[the shell server (shell.DOMAIN-NAME)]),
	[sys_shell_host="$withval"])

AC_ARG_WITH(sys-users-host,
	AC_HELP_STRING([--with-sys-users-host],       
		[the users server (users.DOMAIN-NAME)]),
	[sys_users_host="$withval"])

AC_ARG_WITH(sys-docs-host,
	AC_HELP_STRING([--with-sys-docs-host],        
		[the docs server (docs.DOMAIN-NAME)]),
	[sys_docs_host="$withval"])

AC_ARG_WITH(sys-lists-host,
	AC_HELP_STRING([--with-sys-lists-host],       
		[the lists server (lists.DOMAIN-NAME)]),
	[sys_lists_host="$withval"])

AC_ARG_WITH(sys-dns1-host,
	AC_HELP_STRING([--with-sys-dns1-host],
		[the primary nameserver (ns1.DOMAIN-NAME)]),
	[sys_dns1_host="$withval"])

AC_ARG_WITH(sys-dns2-host,
	AC_HELP_STRING([--with-sys-dns2-host],
		[the secondary nameserver (ns2.DOMAIN-NAME)]),
	[sys_dns2_host="$withval"])

AC_ARG_WITH(sys-dbhost,
	AC_HELP_STRING([--with-sys-dbhost],       
		[the DB server (localhost)]),
	[sys_dbhost="$withval"])

AC_ARG_WITH(sys-dbreadhost,
	AC_HELP_STRING([--with-sys-dbreadhost],       
		[the readonly DB server (localhost)]),
	[sys_dbreadhost="$withval"])

AC_ARG_WITH(sys-dbreaddb,
	AC_HELP_STRING([--with-sys-dbreaddb],       
		[databases that are read only (stats)]),
	[sys_dbreaddb="$withval"])

AC_ARG_WITH(sys-dbname,
	AC_HELP_STRING([--with-sys-dbname],       
		[name of database (alexandria)]),
	[sys_dbname="$withval"])

AC_ARG_WITH(sys-dbuser,
	AC_HELP_STRING([--with-sys-dbuser],       
		[user to access DB as (www)]),
	[sys_dbuser="$withval"])

AC_ARG_WITH(sys-dbpasswd,
	AC_HELP_STRING([--with-sys-dbpasswd],       
		[use password to access DB]),
	[sys_dbpasswd="$withval"])



#############################################################################
# optional features (LDAP, JABBER etc )
#############################################################################



#############################################################################
# LDAP

AC_ARG_ENABLE(ldap,
	AC_HELP_STRING([--enable-ldap],
		[enable LDAP (no)]),
	[sys_use_ldap=1])

AC_ARG_WITH(sys-ldap-host,
	AC_HELP_STRING([--with-sys-ldap-host],
		[LDAP server (ldap.DOMAIN-NAME)]),
	[sys_ldap_host="$withval"])

AC_ARG_WITH(sys-ldap-base-dn,
	AC_HELP_STRING([--with-sys-ldap-base-dn],
		[LDAP base dn (dc=DOMAIN-NAME)]),
	[sys_ldap_base_dn="$withval"])

AC_ARG_WITH(sys-ldap-bind-dn,
	AC_HELP_STRING([--with-sys-ldap-bind-dn],
		[LDAP bind dn (cn=SF_robot,$LDAP_BASE_DN)]),
	[sys_ldap_bind_dn="$withval"])

AC_ARG_WITH(sys-ldap-admin-dn,
	AC_HELP_STRING([--with-sys-ldap-admin-dn],
		[LDAP admin dn ($LDAP_ADMIN_DN)]),
	[sys_ldap_admin_dn="$withval"])

AC_ARG_WITH(sys-ldap-password,
	AC_HELP_STRING([--with-sys-ldap-password],
		[LDAP password]),
	[sys_ldap_passwd="$withval"])


#############################################################################
#  JABBER

AC_ARG_ENABLE(jabber,
	AC_HELP_STRING([--enable-jabber],
		[enable Jabber (no)]),
	[sys_use_jaber=1])

AC_ARG_WITH(sys-jabber-host,
	AC_HELP_STRING([--with-sys-jabber-host],
		[Jabber server (jabber.DOMAIN-NAME)]),
	[sys_jabber_host="$withval"])

AC_ARG_WITH(sys-jabber-user,
	AC_HELP_STRING([--with-sys-jabber-user],
		[Jabber messages come from USER (noreply)]),
	[sys_jabber_user="$withval"])

AC_ARG_WITH(sys-jabber-pass,
	AC_HELP_STRING([--with-sys-jabber-pass],
		[JABBER password (temp123)]),
	[sys_jabber_pass="$withval"])


#############################################################################
# Paths
#############################################################################

AC_ARG_WITH(sys-path-to-jpgraph,
	AC_HELP_STRING([--with-sys-path-to-jpgraph],
		[path to Jpgraph]),
	[sys_path_to_jpgraph="$withval"])

AC_ARG_WITH(sys-upload-dir,
	AC_HELP_STRING([--with-sys-upload-dir],
		[location of upload, must be outside of DocumentRoot]),
	[sys_upload_dir="$withval"])

AC_ARG_WITH(homedir-prefix,
	AC_HELP_STRING([--with-homedir-prefix],
		[homedir prefix ($HOME_DIR)]),
	[homedir_prefix="$withval"])

AC_ARG_WITH(groupdir-prefix,
	AC_HELP_STRING([--with-groupdir-prefix],
		[groups prefix ($GROUP_DIR)]),
	[groupdir_prefix="$withval"])

#############################################################################
#  Misc config
#############################################################################

AC_ARG_WITH(db,
	AC_HELP_STRING([--with-db=DB],
		[DB to use (only pgsql currently supported)]),
	[sys_server="$withval"])

AC_ARG_WITH(scm,
	AC_HELP_STRING([--with-scm=SCM],
		[SCM system to use (only CVS currently supported)]),
	[sys_scm="$withval"])

AC_ARG_WITH(sys-name,
	AC_HELP_STRING([--with-sys-name],      
		[the name of the GForge installation (prodigy)]),
	[sys_name="$withval"])

AC_ARG_WITH(sys-news-group,
	AC_HELP_STRING([--with-sys-news-group],      
		[the group id for news (3)]),
	[sys_news_group="$withval"])

AC_ARG_WITH(sys-stats-group,
	AC_HELP_STRING([--with-sys-stats-group],      
		[the group id for stats (2)]),
	[sys_stats_group="$withval"])

AC_ARG_WITH(sys-peer-rating-group,
	AC_HELP_STRING([--with-sys-peer-rating-group],      
		[the group id for peer ratings (4)]),
	[sys_peer_rating_group="$withval"])

AC_ARG_WITH(default-trove-cat,
	AC_HELP_STRING([--with-default-trove-cat],      
		[the default Trove category (18)]),
	[default_trove_cat="$withval"])

AC_ARG_WITH(sys-theme,
	AC_HELP_STRING([--with-sys-theme],
		[Theme to use by default (gforge)]),
	[sys_theme="$withval"])

AC_ARG_WITH(sys-lang,
	AC_HELP_STRING([--with-sys-lang],      
		[language to use (English)]),
	[sys_lang="$withval"])

AC_ARG_WITH(sys-session-key,
	AC_HELP_STRING([--with-sys-session-key],
		[the session key (generate one using ps)]),
	[sys_session_key="$withval"
		AC_MSG_RESULT([Session key... $SESSION_KEY])
	],
	[
		sys_session_key="`ps aux | $HASHING_PROG`"
		if test "$HASHING_PROG" == "sum"
		then
			POST_PROC=" | $AWK '{print \$1}'"
			sys_session_key="`echo \"$sys_session_key\" | $AWK '{print \$1}' `"
		fi
		AC_MSG_RESULT([Session key (using "ps aux | $HASHING_PROG$POST_PROC")... $sys_session_key])
	])


#############################################################################
# check to see if the values given are okay.
#############################################################################

AC_MSG_CHECKING([whether sys_upload_dir directory exists])
if test -d "$sys_upload_dir"
then
	AC_MSG_RESULT([Yes.])
	if `echo $sys_upload_dir | grep -- "${sys_urlroot}"  > /dev/null `
	then
		AC_MSG_ERROR([sys_upload_dir must not be under Document Root ($sys_urlroot).])
	fi
else
	AC_MSG_ERROR([No.])
fi

AC_MSG_CHECKING([whether "$sys_lang" is supported])
if test -f "$TOP_DIR/www/include/languages/$sys_lang.tab"
then
	AC_MSG_RESULT([Yes.])
else
	AC_MSG_ERROR([No.])
fi

AC_MSG_CHECKING([whether the theme "$sys_theme" exists])
if test -d "$TOP_DIR/www/themes/$sys_theme"
then
	AC_MSG_RESULT([Yes.])
else
	AC_MSG_ERROR([$sys_theme theme does not exist.])
fi


#############################################################################
# The names of hosts that provide services
AC_SUBST(sys_default_domain)
AC_SUBST(sys_fallback_domain)
AC_SUBST(sys_internal_network)
AC_SUBST(sys_cvs_host)
AC_SUBST(sys_cvs_single_host)
AC_SUBST(sys_download_host)
AC_SUBST(sys_upload_host)
AC_SUBST(sys_shell_host)
AC_SUBST(sys_users_host)
AC_SUBST(sys_docs_host)
AC_SUBST(sys_lists_host)
AC_SUBST(sys_dns1_host)
AC_SUBST(sys_dns2_host)

# DB
AC_SUBST(sys_server)
AC_SUBST(sys_dbhost)
AC_SUBST(sys_dbreadhost)
AC_SUBST(sys_dbreaddb)
AC_SUBST(sys_dbname)
AC_SUBST(sys_dbuser)
AC_SUBST(sys_dbpasswd)

#############################################################################
# Paths
AC_SUBST(TOP_DIR)
AC_SUBST(sys_upload_dir)
AC_SUBST(sys_path_to_jpgraph)
AC_SUBST(homedir_prefix)
AC_SUBST(groupdir_prefix)
AC_SUBST(sys_urlroot)

#############################################################################
# Misc variables
AC_SUBST(sys_name)
AC_SUBST(sys_theme)
AC_SUBST(sys_lang)
AC_SUBST(sys_session_key)
AC_SUBST(sys_scm)

# UIDs
AC_SUBST(sys_news_group)
AC_SUBST(sys_stats_group)
AC_SUBST(sys_peer_rating_group)
AC_SUBST(default_trove_cat)

#############################################################################
# Options

# LDAP
AC_SUBST(sys_use_ldap)
AC_SUBST(sys_ldap_host)
AC_SUBST(sys_ldap_base_dn)
AC_SUBST(sys_ldap_bind_dn)
AC_SUBST(sys_ldap_admin_dn)
AC_SUBST(sys_ldap_passwd)

# JABBER
AC_SUBST(sys_use_jabber)
AC_SUBST(sys_jabber_host)
AC_SUBST(sys_jabber_user)
AC_SUBST(sys_jabber_pass)

#############################################################################
# process these files
AC_CONFIG_FILES([
	local.inc
	sample-apache.vhost
])

#############################################################################
# give a summary of the config for quick debugging...

AC_CONFIG_COMMANDS_POST([
echo ""
echo "Some key variables:-"
echo ""
echo "  sys_default_domain = $sys_default_domain"
echo "        sys_cvs_host = $sys_cvs_host"
echo "   sys_download_host = $sys_download_host"
echo "     sys_upload_host = $sys_upload_host"
echo "      sys_shell_host = $sys_shell_host"
echo "      sys_users_host = $sys_users_host"
echo "       sys_docs_host = $sys_docs_host"
echo "      sys_lists_host = $sys_lists_host"
echo "       sys_dns1_host = $sys_dns1_host"
echo "       sys_dns2_host = $sys_dns2_host"
echo "          sys_dbhost = $sys_dbhost"
echo "      sys_dbreadhost = $sys_dbreadhost"
echo "          sys_dbname = $sys_dbname"
echo "        sys_dbreaddb = $sys_dbreaddb"
echo "          sys_dbuser = $sys_dbuser"
if test $sys_use_ldap == 1
then
echo ""
echo "       sys_ldap_host = $sys_ldap_host"
echo "    sys_ldap_base_dn = $sys_ldap_base_dn"
echo "    sys_ldap_bind_dn = $sys_ldap_bind_dn"
echo "   sys_ldap_admin_dn = $sys_ldap_admin_dn"
fi
if test $sys_use_jabber == 1
then
echo ""
echo "     sys_jabber_host = $sys_jabber_host"
echo "     sys_jabber_user = $sys_jabber_user"
fi
echo ""
echo "            sys_name = $sys_name"
echo ""
echo "         sys_urlroot = $sys_urlroot"
echo "      sys_upload_dir = $sys_upload_dir"
echo " sys_path_to_jpgraph = $sys_path_to_jpgraph"
echo ""
echo "     sys_session_key = $sys_session_key"
echo ""
])

AC_OUTPUT()
