# Read-only access for public repos
<Location /anonscm/svn/>
  DAV svn
  SVNParentPath {scmsvn/repos_path}
  # allow read-only browsing
  <LimitExcept GET PROPFIND OPTIONS REPORT>
  </LimitExcept>
</Location>

# Authentification for macro 'ScmsvnUser'
<LocationMatch "^/authscm/[^/]+/svn">
  # how to authenticate a user
  AuthType Basic
  AuthName "SCM for FusionForge"
  AuthUserFile {core/data_path}/svnroot-authfile
  # Rely on macro 'ScmsvnUser' and Unix privs
  #AuthzSVNAccessFile {core/data_path}/svnroot-access
</LocationMatch>

# ViewVC
# Authentified via cookie in viewvc.php:
#<LocationMatch "^/authscm/[^/]+/viewvc">
#  AuthType Basic
#  AuthName "SCM for FusionForge"
#  AuthUserFile {core/data_path}/svnroot-authfile
#</LocationMatch>
ScriptAlias /anonscm/viewvc {core/source_path}/plugins/scmsvn/cgi-bin/viewvc.cgi
ScriptAliasMatch ^/authscm/[^/]+/viewvc(.*) {core/source_path}/plugins/scmsvn/cgi-bin/viewvc.cgi$1
#Alias /doc/viewvc /usr/share/viewvc/docroot
<Directory {core/source_path}/plugins/scmsvn/cgi-bin>
  Require all granted
</Directory>

# Redirect URLs from FF < 6.0
<IfModule mod_authz_svn.c>
  Alias /svn {core/source_path}/www/plugins/scmsvn/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  Redirect permanent /svn /anonscm/svn/
</IfModule>

# For reference, we could do without svnroot-authfile, but
# mod-auth-pgsql isn't packaged for RPM :/
#<LocationMatch "^/authscm/[^/]+/svn">
#  AuthType Basic
#  AuthName "SCM for FusionForge"
#  AuthBasicProvider     pgsql
#  Auth_PG_host          127.0.0.1
#  Auth_PG_user          fusionforge_nss
#  #Auth_PG_pwd          <no password>
#  Auth_PG_database      fusionforge
#  Auth_PG_pwd_table     nss_passwd
#  Auth_PG_uid_field     login
#  Auth_PG_pwd_field     passwd
#  Auth_PG_encrypted     on
#  Auth_PG_hash_type     CRYPT
#</LocationMatch>
