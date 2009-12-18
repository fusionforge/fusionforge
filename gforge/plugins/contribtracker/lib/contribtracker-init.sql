CREATE SEQUENCE plugin_contribtracker_legal_structure_pk_seq ;
CREATE TABLE plugin_contribtracker_legal_structure (
       struct_id integer DEFAULT nextval('plugin_contribtracker_legal_structure_pk_seq') NOT NULL,
       struct_name text DEFAULT '' NOT NULL,
       PRIMARY KEY (struct_id)
) ;

CREATE SEQUENCE plugin_contribtracker_role_pk_seq ;
CREATE TABLE plugin_contribtracker_role (
       role_id integer DEFAULT nextval('plugin_contribtracker_role_pk_seq') NOT NULL,
       role_name text,
       role_description text,
       PRIMARY KEY (role_id)
) ;

CREATE SEQUENCE plugin_contribtracker_actor_pk_seq ;
CREATE TABLE plugin_contribtracker_actor (
       actor_id integer DEFAULT nextval('plugin_contribtracker_actor_pk_seq') NOT NULL,
       actor_name text,
       actor_address text,
       actor_email text,
       actor_description text,
       struct_id integer,
       PRIMARY KEY (actor_id),
       FOREIGN KEY (struct_id) REFERENCES plugin_contribtracker_legal_structure (struct_id)
) ;

CREATE SEQUENCE plugin_contribtracker_contribution_pk_seq ;
CREATE TABLE plugin_contribtracker_contribution (
       contrib_id integer DEFAULT nextval('plugin_contribtracker_contribution_pk_seq') NOT NULL,
       contrib_name text,
       contrib_date int,
       contrib_description text,
       group_id integer,
       PRIMARY KEY (contrib_id),
       FOREIGN KEY (group_id) REFERENCES groups (group_id)
) ;

CREATE SEQUENCE plugin_contribtracker_participation_pk_seq ;
CREATE TABLE plugin_contribtracker_participation (
       participation_id integer DEFAULT nextval('plugin_contribtracker_participation_pk_seq') NOT NULL,
       contrib_id integer,
       actor_id integer,
       role_id integer,
       PRIMARY KEY (participation_id),
       FOREIGN KEY (contrib_id) REFERENCES plugin_contribtracker_contribution (contrib_id),
       FOREIGN KEY (actor_id) REFERENCES plugin_contribtracker_actor (actor_id),
       FOREIGN KEY (role_id) REFERENCES plugin_contribtracker_role (role_id)
) ;
