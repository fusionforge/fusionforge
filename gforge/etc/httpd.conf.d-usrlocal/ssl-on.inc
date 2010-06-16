<IfModule mod_ssl.c>
  SSLEngine on
  
  SSLCertificateFile {sys_sslcrt}
  SSLCertificateKeyFile {sys_sslkey}
  {sys_ssl_apache_extra_cmd}
  
  <Files ~ "\.(cgi|shtml)$">
    SSLOptions +StdEnvVars
  </Files>
  
  <Directory "/usr/lib/cgi-bin">
    SSLOptions +StdEnvVars
  </Directory>
  
  SetEnvIf User-Agent ".*MSIE.*" nokeepalive ssl-unclean-shutdown
</IfModule>

<IfModule apache_ssl.c>
  SSLEnable
  
  SetEnvIf User-Agent ".*MSIE.*" nokeepalive ssl-unclean-shutdown
</IfModule>
