<IfModule mod_authz_svn.c>
  Alias /svn {core/source_path}/www/plugins/scmsvn/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  Redirect permanent /svn /anonscm/svn/
</IfModule>

ScriptAlias /anonscm/viewvc {core/source_path}/plugins/scmsvn/cgi-bin/viewvc.cgi
ScriptAliasMatch ^/authscm/[^/]+/viewvc(.*) {core/source_path}/plugins/scmsvn/cgi-bin/viewvc.cgi$1
<Directory {core/source_path}/plugins/scmsvn/cgi-bin/>
  <IfVersion >= 2.3>
    Require all granted
  </IfVersion>
  <IfVersion < 2.3>
    order allow,deny
    allow from all
  </IfVersion>
</Directory>
