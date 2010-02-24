CREATE SEQUENCE plugin_contribtracker_legal_structure_pk_seq ;
CREATE TABLE plugin_contribtracker_legal_structure (
       struct_id integer DEFAULT nextval('plugin_contribtracker_legal_structure_pk_seq') PRIMARY KEY,
       name text UNIQUE NOT NULL
) ;

CREATE SEQUENCE plugin_contribtracker_role_pk_seq ;
CREATE TABLE plugin_contribtracker_role (
       role_id integer DEFAULT nextval('plugin_contribtracker_role_pk_seq') PRIMARY KEY,
       name text UNIQUE NOT NULL,
       description text DEFAULT '' NOT NULL
) ;

CREATE SEQUENCE plugin_contribtracker_actor_pk_seq ;
CREATE TABLE plugin_contribtracker_actor (
       actor_id integer DEFAULT nextval('plugin_contribtracker_actor_pk_seq') PRIMARY KEY,
       name text UNIQUE NOT NULL,
       url text DEFAULT '' NOT NULL,
       email text DEFAULT '' NOT NULL,
       description text DEFAULT '' NOT NULL,
       logo text DEFAULT '' NOT NULL,
       struct_id integer NOT NULL REFERENCES plugin_contribtracker_legal_structure
) ;

CREATE SEQUENCE plugin_contribtracker_contribution_pk_seq ;
CREATE TABLE plugin_contribtracker_contribution (
       contrib_id integer DEFAULT nextval('plugin_contribtracker_contribution_pk_seq') PRIMARY KEY,
       name text DEFAULT '' NOT NULL,
       date int DEFAULT 0 NOT NULL,
       description text DEFAULT '' NOT NULL,
       group_id integer NOT NULL REFERENCES groups ON DELETE CASCADE
) ;

CREATE SEQUENCE plugin_contribtracker_participation_pk_seq ;
CREATE TABLE plugin_contribtracker_participation (
       participation_id integer DEFAULT nextval('plugin_contribtracker_participation_pk_seq') PRIMARY KEY,
       contrib_id integer NOT NULL REFERENCES plugin_contribtracker_contribution ON DELETE CASCADE,
       actor_id integer NOT NULL REFERENCES plugin_contribtracker_actor,
       role_id integer NOT NULL REFERENCES plugin_contribtracker_role,
       index integer NOT NULL,
       CONSTRAINT index_unicity UNIQUE(contrib_id,index)
) ;
