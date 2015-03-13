<Location ${FF__core__url_prefix}/plugins/authhttpd/post-login.php>
  # You'll need to customize the following according to your needs

  AuthType Basic
  AuthName "Forge Authentication"

  AuthUserFile ${FF__core__config_path}/authhttpd.authfile
  Require valid-user
</Location>
