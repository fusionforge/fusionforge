#! /bin/sh

echo "#"
echo "# ForumML Plugin install"
echo "#"

## Chown ForumMl temp and data dir
touch /var/log/gforge/forumml_hook.log
chown root.list /var/log/gforge/forumml_hook.log
chmod 664 /var/log/gforge/forumml_hook.log
chown gforge.gforge /var/spool/forumml /var/lib/gforge/forumml
chown gforge.gforge /usr/share/gforge/plugins/forumml/bin/mail_2_DBFF.pl
chmod 06755 /usr/share/gforge/plugins/forumml/bin/mail_2_DBFF.pl

## Update Mailman config to enable the Hook
if ! grep -q ^PUBLIC_EXTERNAL_ARCHIVER /usr/lib/mailman/Mailman/mm_cfg.py
then
        cat <<EOF >> /usr/lib/mailman/Mailman/mm_cfg.py
# ForumML Plugin
PUBLIC_EXTERNAL_ARCHIVER = '/usr/share/gforge/plugins/forumml/bin/mail_2_DBFF.pl %(listname)s ;'
PRIVATE_EXTERNAL_ARCHIVER = '/usr/share/gforge/plugins/forumml/bin/mail_2_DBFF.pl %(listname)s ;'
EOF
fi

## restart mailman
invoke-rc.d mailman restart
