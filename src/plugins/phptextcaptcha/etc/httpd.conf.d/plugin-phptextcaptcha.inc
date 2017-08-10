Alias /plugins/phptextcaptcha ${FF__core__data_path}/plugins/phptextcaptcha
<Directory ${FF__core__data_path}/plugins/phptextcaptcha >
  Include ${FF__core__config_path}/httpd.conf.d/auth-main.inc
</Directory>

