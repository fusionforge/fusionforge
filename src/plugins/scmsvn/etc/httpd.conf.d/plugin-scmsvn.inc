<IfModule mod_authz_svn.c>
  Alias /svn {core/plugins_path}/scmsvn/common/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  Redirect permanent /svn /anonscm/svn/
</IfModule>
