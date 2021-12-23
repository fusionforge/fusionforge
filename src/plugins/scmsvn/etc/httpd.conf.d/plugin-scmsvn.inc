# Redirect URLs from FF < 6.0 (web_host -> scm_host)
<IfModule mod_authz_svn.c>
  Alias /svn ${FF__core__source_path}/www/plugins/scmsvn/redirect-to-itk.php
</IfModule>
<IfModule !mod_authz_svn.c>
  <IfDefine FF__core__https_port>
    Redirect permanent /svn https://${FF__core__scm_host}:${FF__core__https_port}/anonscm/svn/
  </IfDefine>
  <IfDefine !FF__core__https_port>
    Redirect permanent /svn https://${FF__core__scm_host}/anonscm/svn/
  </IfDefine>
</IfModule>
