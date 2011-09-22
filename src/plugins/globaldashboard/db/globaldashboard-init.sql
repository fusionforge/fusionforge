CREATE SEQUENCE plugin_globaldashboard_user_forge_account_pk_seq start 1 increment 1 minvalue 1 cache 1 ;
CREATE TABLE plugin_globaldashboard_user_forge_account (
	account_id integer PRIMARY KEY DEFAULT NEXTVAL('plugin_globaldashboard_user_forge_account_pk_seq') ,
	user_id integer NOT NULL REFERENCES users(user_id) ,
	forge_account_login_name varchar(32) DEFAULT '' NOT NULL ,
	forge_account_password varchar(32) DEFAULT '' NOT NULL ,
	forge_software integer DEFAULT 1 NOT NULL ,
	forge_account_domain varchar(250) DEFAULT '' NOT NULL ,
	forge_account_uri varchar(250) DEFAULT '' NOT NULL ,
	forge_account_is_foaf integer DEFAULT 0 ,
	forge_oslc_discovery_uri varchar(250) DEFAULT NULL ,
	forge_account_rss_uri varchar(250) DEFAULT NULL ,
	forge_account_soap_wsdl_uri varchar(250) DEFAULT NULL
) ;

CREATE SEQUENCE plugin_globaldashboard_account_discovery_pk_seq start 1 increment 1 minvalue 1 cache 1 ;
CREATE TABLE plugin_globaldashboard_account_discovery (
	account_discovery_id integer PRIMARY KEY DEFAULT NEXTVAL('plugin_globaldashboard_account_discovery_pk_seq') ,
	account_id integer NOT NULL REFERENCES plugin_globaldashboard_user_forge_account(account_id) ON DELETE CASCADE ,
	projects_discovery_method integer DEFAULT NULL ,
	artifacts_discovery_method integer DEFAULT NULL 
) ;
