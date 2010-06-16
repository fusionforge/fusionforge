<IfModule mod_ssl.c>
  SSLEngine on
  
  SSLCertificateFile /opt/fusionforge/etc/ssl-cert.pem
  SSLCertificateKeyFile /opt/fusionforge/etc/ssl-cert.key
  # Add extra SSL configuration (e.g. SSLCACertificatePath) here
  
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
