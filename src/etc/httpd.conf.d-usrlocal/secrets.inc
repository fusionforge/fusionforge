# Settings for configuration variables that are normally kept in a configuration file unreadable to www-data
# Restart Apache after changing these

RequestHeader unset GForgeDbhost
RequestHeader append GForgeDbhost "{core/database_host}"

RequestHeader unset GForgeDbport
RequestHeader append GForgeDbport "{core/database_port}"

RequestHeader unset GForgeDbname
RequestHeader append GForgeDbname "{core/database_name}"

RequestHeader unset GForgeDbuser
RequestHeader append GForgeDbuser "{core/database_user}"

RequestHeader unset GForgeDbpasswd
RequestHeader append GForgeDbpasswd "{core/database_password}"

RequestHeader unset GForgeLdapPasswd
RequestHeader append GForgeLdapPasswd "{core/ldap_password}"

RequestHeader unset GForgeJabberPasswd
RequestHeader append GForgeJabberPasswd "{core/jabber_password}"
