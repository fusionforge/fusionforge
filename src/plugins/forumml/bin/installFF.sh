#! /bin/sh

echo "#"
echo "# ForumML Plugin install"
echo "#"

## Chown ForumMl temp and data dir
chown gforge.list $(forge_get_config log_path)/forumml
chmod 775 $(forge_get_config log_path)/forumml
chmod g+s $(forge_get_config log_path)/forumml
chown list.list /var/spool/forumml
chown list.list $(forge_get_config data_path)/forumml
#chown gforge.gforge /var/spool/forumml /var/lib/fusionforge/forumml
#chown gforge.gforge $(forge_get_config plugins_path)/forumml/bin/mail_2_DBFF.pl
#chmod 06755 $(forge_get_config plugins_path)/forumml/bin/mail_2_DBFF.pl

## Update Mailman config to enable the Hook
if ! grep -q ^PUBLIC_EXTERNAL_ARCHIVER /usr/lib/mailman/Mailman/mm_cfg.py
then
        cat <<EOF >> /usr/lib/mailman/Mailman/mm_cfg.py
# ForumML Plugin
PUBLIC_EXTERNAL_ARCHIVER = '$(forge_get_config plugins_path)/forumml/bin/mail_2_DBFF.pl %(listname)s ;'
PRIVATE_EXTERNAL_ARCHIVER = '$(forge_get_config plugins_path)/forumml/bin/mail_2_DBFF.pl %(listname)s ;'
EOF
fi

## restart mailman
invoke-rc.d mailman restart
