<Proxy http://127.0.0.1:8081/*>
  Allow from all
</Proxy>

<Location {core/url_prefix}scm/loggerhead/>
  ProxyPass http://127.0.0.1:8081/
  ProxyPassReverse http://127.0.0.1:8081/
</Location>
