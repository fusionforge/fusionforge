Alias /plugins/phpcaptcha ${FF__phpcaptcha__phpcaptcha_path}
<Directory ${FF__phpcaptcha__phpcaptcha_path}>
  Include ${FF__core__config_path}/httpd.conf.d/auth-main.inc
</Directory>
