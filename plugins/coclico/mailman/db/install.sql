## 
## Sql Install Script
##
 CREATE TABLE IF NOT EXISTS `plugin_mailman` (
listname varchar( 100 ) NOT NULL ,
address varchar( 255 ) NOT NULL ,
hide enum( 'Y', 'N' ) NOT NULL default 'N',
nomail enum( 'Y', 'N' ) NOT NULL default 'N',
ack enum( 'Y', 'N' ) NOT NULL default 'Y',
not_metoo enum( 'Y', 'N' ) NOT NULL default 'Y',
digest enum( 'Y', 'N' ) NOT NULL default 'N',
plain enum( 'Y', 'N' ) NOT NULL default 'N',
PASSWORD varchar( 255 ) NOT NULL default '!',
lang varchar( 255 ) NOT NULL default 'en',
name varchar( 255 ) default NULL ,
one_last_digest enum( 'Y', 'N' ) NOT NULL default 'N',
user_options bigint( 20 ) NOT NULL default 0,
delivery_status INT( 10 ) NOT NULL default 0,
topics_userinterest varchar( 255 ) default NULL ,
delivery_status_timestamp datetime default '0000-00-00 00:00:00',
bi_cookie varchar( 255 ) default NULL ,
bi_score double NOT NULL default '0',
bi_noticesleft double NOT NULL default '0',
bi_lastnotice date NOT NULL default '0000-00-00',
bi_date date NOT NULL default '0000-00-00'
); 
INSERT INTO service(group_id, label, description, short_name, link, is_active, is_used, scope, rank) VALUES ( 100 , 'Mailman' , 'Manage mailing lists' , 'mailman', '/plugins/mailman/index.php?group_id=$group_id', 1 , 0 , 'system',  230 );

        
