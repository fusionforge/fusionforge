# Redirect URLs from FF < 6.0 (web_host -> scm_host)
<IfModule mod_authz_svn.c>
  Alias /svn {core/source_path}/www/plugins/scmsvn/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  Redirect permanent /svn https://{core/scm_host}/anonscm/svn/
</IfModule>
