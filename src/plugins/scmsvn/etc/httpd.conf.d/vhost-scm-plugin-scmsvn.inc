<Directory {scmsvn/repos_path}>
  Include {core/config_path}/httpd.conf.d/disable-scripts.inc
</Directory>

# Read-only access for public repos
<Location /anonscm/svn/>
  DAV svn
  SVNParentPath {scmsvn/repos_path}
  # allow read-only browsing
  <LimitExcept GET PROPFIND OPTIONS REPORT>
  </LimitExcept>
</Location>

# ViewVC
ScriptAliasMatch ^/authscm/[^/]+/viewvc(.*) {core/plugins_path}/scmsvn/libexec/viewvc.cgi$1
# Authentified via cookie in viewvc.php:
<LocationMatch "^/authscm/[^/]+/viewvc">
  Satisfy Any
</LocationMatch>
ScriptAlias /anonscm/viewvc {core/plugins_path}/scmsvn/libexec/viewvc.cgi
#Alias /doc/viewvc /usr/share/viewvc/docroot
<Directory {core/plugins_path}/scmsvn/libexec>
  Require all granted
</Directory>

# Redirect URLs from FF < 6.0 (scm_host -> scm_host)
# Note: ViewVC URLs are backward-compatible through proxying in viewvc.php
<IfModule mod_authz_svn.c>
  Alias /svn {core/source_path}/www/plugins/scmsvn/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  Redirect permanent /svn /anonscm/svn/
</IfModule>
