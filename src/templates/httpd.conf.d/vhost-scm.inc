ServerName {core/scm_host}

SetEnvIf Request_URI . ITKUID=www-data
SetEnvIf Request_URI . ITKGID=www-data

<IfVersion >= 2.3>
  IncludeOptional {core/config_path}/httpd.conf.d/plugin-scm*.inc
  IncludeOptional {core/config_path}/httpd.conf.d/vhost-scm-plugin-scm*.inc
  IncludeOptional {core/data_path}/scm*-auth*.inc
</IfVersion>
<IfVersion < 2.3>
  Include {core/config_path}/httpd.conf.d/plugin-scm*.inc
  Include {core/config_path}/httpd.conf.d/vhost-scm-plugin-scm*.inc
  Include {core/data_path}/scm*-auth*.inc
</IfVersion>

AssignUserIDExpr %{reqenv:ITKUID}
AssignGroupIDExpr %{reqenv:ITKGID}
