<IfModule mod_authz_svn.c>
  Alias /svn {core/source_path}/www/plugins/scmsvn/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  Redirect permanent /svn /anonscm/svn/
</IfModule>
