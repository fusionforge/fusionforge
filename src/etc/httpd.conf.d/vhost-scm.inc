ServerName {core/scm_host}

<IfVersion >= 2.3>
  IncludeOptional {core/config_path}/httpd.conf.d/plugin-scm*.inc
</IfVersion>
<IfVersion < 2.3>
  Include {core/config_path}/httpd.conf.d/plugin-scm*.inc
</IfVersion>
