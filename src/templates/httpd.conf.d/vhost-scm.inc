ServerName {core/scm_host}

SetEnvIf Request_URI . ITKUID={core/apache_user}
SetEnvIf Request_URI . ITKGID={core/apache_group}

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
