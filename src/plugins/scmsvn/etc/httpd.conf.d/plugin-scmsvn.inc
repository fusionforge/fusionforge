<Location /anonscm/svn/>
  DAV svn
  SVNParentPath {scmsvn/repos_path}
  # allow read-only browsing
  <LimitExcept GET PROPFIND OPTIONS REPORT>
  </LimitExcept>
</Location>

<LocationMatch "^/authscm/[^/]+/svn">
  # how to authenticate a user
  AuthType Basic
  AuthName "SCM for FusionForge"
  AuthUserFile {core/data_path}/svnroot-authfile
  # Rely on macro 'ScmsvnUser' and Unix privs
  #AuthzSVNAccessFile {core/data_path}/svnroot-access
</LocationMatch>

<LocationMatch "^/authscm/[^/]+/viewvc">
  # TODO: let's find a way to authenticate from viewvc.php
  #AuthType Basic
  #AuthName "SCM for FusionForge"
  #AuthUserFile {core/data_path}/svnroot-authfile
</LocationMatch>

ScriptAlias /anonscm/viewvc {core/source_path}/plugins/scmsvn/cgi-bin/viewvc.cgi
ScriptAliasMatch ^/authscm/[^/]+/viewvc(.*) {core/source_path}/plugins/scmsvn/cgi-bin/viewvc.cgi$1
<Directory {core/source_path}/plugins/scmsvn/cgi-bin>
  Require all granted
</Directory>
#Alias /doc/viewvc /usr/share/viewvc/docroot

# Redirect URLs from FF < 6.0
<IfModule mod_authz_svn.c>
  Alias /svn {core/source_path}/www/plugins/scmsvn/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  Redirect permanent /svn /anonscm/svn/
</IfModule>
