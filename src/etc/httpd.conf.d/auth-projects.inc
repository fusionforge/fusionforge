# HTTP Basic Auth hook for the project vhosts

<IfVersion >= 2.3>
  Require all granted
</IfVersion>
<IfVersion < 2.3>
  order allow,deny
  allow from all
</IfVersion>
