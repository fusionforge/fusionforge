<Proxy http://127.0.0.1:8081/*>
  Allow from all
</Proxy>

<Location /scm/loggerhead/>
  ProxyPass http://127.0.0.1:8081/
  ProxyPassReverse http://127.0.0.1:8081/
</Location>
