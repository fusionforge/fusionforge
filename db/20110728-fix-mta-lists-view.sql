CREATE OR REPLACE VIEW mta_lists AS
        SELECT
                list_name,
                '|/var/lib/mailman/mail/mailman post ' || list_name AS post_address,
                '|/var/lib/mailman/mail/mailman admin ' || list_name AS admin_address,
                '|/var/lib/mailman/mail/mailman bounces ' || list_name AS bounces_address,
                '|/var/lib/mailman/mail/mailman confirm ' || list_name AS confirm_address,
                '|/var/lib/mailman/mail/mailman join ' || list_name AS join_address,
                '|/var/lib/mailman/mail/mailman leave ' || list_name AS leave_address,
                '|/var/lib/mailman/mail/mailman owner ' || list_name AS owner_address,
                '|/var/lib/mailman/mail/mailman request ' || list_name AS request_address,
                '|/var/lib/mailman/mail/mailman subscribe ' || list_name AS subscribe_address,
                '|/var/lib/mailman/mail/mailman unsubscribe ' || list_name AS unsubscribe_address
        FROM mail_group_list
        WHERE status = 3 OR status = 4;
