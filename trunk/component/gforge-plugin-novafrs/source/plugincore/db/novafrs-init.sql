--
-- Novaforge is a registered trade mark from Bull S.A.S
-- Copyright (C) 2007 Bull S.A.S.
-- 
-- http://novaforge.org/
--
--
-- This file has been developped within the Novaforge(TM) project from Bull S.A.S
-- and contributed back to GForge community.
--
-- GForge is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- GForge is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this file; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
--
/**
 * @version   $Id: frs-init.sql,v 1.7 2006/11/08 19:44:34 pascal Exp $
 */
 
CREATE SEQUENCE plugin_frs_fr_data_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_frs_fr_data (
    frid integer DEFAULT nextval('plugin_frs_fr_data_pk_seq'::text) UNIQUE NOT NULL,
    stateid integer DEFAULT 0 NOT NULL,
    title character varying(255) DEFAULT ''::character varying NOT NULL,
    data text DEFAULT ''::text NOT NULL,
    updatedate integer DEFAULT 0 NOT NULL,
    createdate integer DEFAULT 0 NOT NULL,
    created_by integer DEFAULT 0 NOT NULL,
    fr_group integer DEFAULT 0 NOT NULL,
    description text,
    language_id integer DEFAULT 1 NOT NULL,
    filename text,
    filetype text,
    group_id integer,
    filesize integer DEFAULT 0 NOT NULL,
    
    status              integer DEFAULT 0 NOT NULL,
    status_modif_by     integer DEFAULT 0 NOT NULL,
    status_modif_date   integer DEFAULT 0 NOT NULL,
    
    author          character varying(150),
    writing_date    character varying(25),
    fr_type        character varying(150),
    reference       character varying(150),
    version         character varying(150),
    
    is_current      smallint DEFAULT 1 NOT NULL,
    frid_current_version integer REFERENCES plugin_frs_fr_data (frid),
    
    fr_chrono      integer NOT NULL,
    fr_observation character varying(250)
);

    
CREATE TABLE plugin_frs_fr_status_table(
    frid       integer REFERENCES plugin_frs_fr_data (frid)  NOT NULL,
    statustype  integer NOT NULL,
    date        character varying(25),
    name        character varying(150),
    description text
);




CREATE TABLE plugin_frs_fr_chrono(
    group_id integer NOT NULL ,
    chrono integer NOT NULL
);
    

CREATE SEQUENCE plugin_frs_fr_groups_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_frs_fr_groups (
    fr_group integer DEFAULT nextval('plugin_frs_fr_groups_pk_seq'::text) UNIQUE NOT NULL,
    groupname character varying(255) DEFAULT ''::character varying NOT NULL,
    group_id integer DEFAULT 0 NOT NULL,
    parent_fr_group integer DEFAULT 0 NOT NULL,
    stateid smallint DEFAULT 0 NOT NULL
);




CREATE SEQUENCE plugin_frs_fr_states_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_frs_fr_states (
    stateid integer DEFAULT nextval('plugin_frs_fr_states_pk_seq'::text) NOT NULL,
    name character varying(255) DEFAULT ''::character varying NOT NULL
);

CREATE TABLE plugin_frs_fr_authorization(
    fr_group integer NOT NULL REFERENCES plugin_frs_fr_groups (fr_group),
    role_id integer NOT NULL REFERENCES role (role_id),
    auth smallint NOT NULL
);
 
 


CREATE VIEW plugin_frs_frdata_vw AS
    SELECT users.user_name, users.realname, users.email, d.group_id, d.frid, d.stateid, d.title, d.updatedate, d.createdate, 
            d.created_by, d.fr_group, d.description, d.language_id, d.filename, d.filetype, d.filesize, 
            d.status, d.status_modif_by, d.status_modif_date, d.fr_observation, d.fr_chrono,
            d.author, d.writing_date, d.fr_type, d.reference, d.version, d.is_current, d.frid_current_version,    
            plugin_frs_fr_states.name AS state_name, plugin_frs_fr_groups.groupname AS group_name, sl.name AS language_name,
            usrstatus.user_name  AS status_user_name, usrstatus.realname  AS status_realname
    FROM 
    (
        ( 
            ((plugin_frs_fr_data d NATURAL JOIN plugin_frs_fr_states) JOIN plugin_frs_fr_groups ON( plugin_frs_fr_groups.fr_group=d.fr_group ) ) 
            JOIN supported_languages sl ON ((sl.language_id = d.language_id))
        ) 
            JOIN users ON (users.user_id = d.created_by)
            JOIN users usrstatus ON( usrstatus.user_id = d.status_modif_by )
    )
;

ALTER TABLE ONLY plugin_frs_fr_data
    ADD CONSTRAINT plugin_frs_fr_data_pkey PRIMARY KEY (frid);

ALTER TABLE ONLY plugin_frs_fr_groups
    ADD CONSTRAINT plugin_frs_fr_groups_pkey PRIMARY KEY (fr_group);

ALTER TABLE ONLY plugin_frs_fr_states
    ADD CONSTRAINT plugin_frs_fr_states_pkey PRIMARY KEY (stateid);

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_languageid_fk
    AFTER INSERT OR UPDATE ON plugin_frs_fr_data
    FROM supported_languages
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_check_ins"('plugin_frs_frdata_languageid_fk', 'plugin_frs_fr_data', 'supported_languages', 'FULL', 'language_id', 'language_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_languageid_fk
    AFTER DELETE ON supported_languages
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_del"('plugin_frs_frdata_languageid_fk', 'plugin_frs_fr_data', 'supported_languages', 'FULL', 'language_id', 'language_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_languageid_fk
    AFTER UPDATE ON supported_languages
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_upd"('plugin_frs_frdata_languageid_fk', 'plugin_frs_fr_data', 'supported_languages', 'FULL', 'language_id', 'language_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_groupid
    AFTER INSERT OR UPDATE ON plugin_frs_fr_data
    FROM groups
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_check_ins"('plugin_frs_frdata_groupid', 'plugin_frs_fr_data', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_groupid
    AFTER DELETE ON groups
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_cascade_del"('plugin_frs_frdata_groupid', 'plugin_frs_fr_data', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_groupid
    AFTER UPDATE ON groups
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_upd"('plugin_frs_frdata_groupid', 'plugin_frs_fr_data', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_frgroupid
    AFTER INSERT OR UPDATE ON plugin_frs_fr_data
    FROM plugin_frs_fr_groups
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_check_ins"('plugin_frs_frdata_frgroupid', 'plugin_frs_fr_data', 'plugin_frs_fr_groups', 'UNSPECIFIED', 'fr_group', 'fr_group');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_frgroupid
    AFTER DELETE ON plugin_frs_fr_groups
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_del"('plugin_frs_frdata_frgroupid', 'plugin_frs_fr_data', 'plugin_frs_fr_groups', 'UNSPECIFIED', 'fr_group', 'fr_group');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_frgroupid
    AFTER UPDATE ON plugin_frs_fr_groups
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_upd"('plugin_frs_frdata_frgroupid', 'plugin_frs_fr_data', 'plugin_frs_fr_groups', 'UNSPECIFIED', 'fr_group', 'fr_group');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_stateid
    AFTER INSERT OR UPDATE ON plugin_frs_fr_data
    FROM plugin_frs_fr_states
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_check_ins"('plugin_frs_frdata_stateid', 'plugin_frs_fr_data', 'plugin_frs_fr_states', 'UNSPECIFIED', 'stateid', 'stateid');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_stateid
    AFTER DELETE ON plugin_frs_fr_states
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_del"('plugin_frs_frdata_stateid', 'plugin_frs_fr_data', 'plugin_frs_fr_states', 'UNSPECIFIED', 'stateid', 'stateid');

CREATE CONSTRAINT TRIGGER plugin_frs_frdata_stateid
    AFTER UPDATE ON plugin_frs_fr_states
    FROM plugin_frs_fr_data
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_upd"('plugin_frs_frdata_stateid', 'plugin_frs_fr_data', 'plugin_frs_fr_states', 'UNSPECIFIED', 'stateid', 'stateid');

CREATE CONSTRAINT TRIGGER plugin_frs_frgroups_groupid
    AFTER INSERT OR UPDATE ON plugin_frs_fr_groups
    FROM groups
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_check_ins"('plugin_frs_frgroups_groupid', 'plugin_frs_fr_groups', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frgroups_groupid
    AFTER DELETE ON groups
    FROM plugin_frs_fr_groups
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_cascade_del"('plugin_frs_frgroups_groupid', 'plugin_frs_fr_groups', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');

CREATE CONSTRAINT TRIGGER plugin_frs_frgroups_groupid
    AFTER UPDATE ON groups
    FROM plugin_frs_fr_groups
    NOT DEFERRABLE INITIALLY IMMEDIATE
    FOR EACH ROW
    EXECUTE PROCEDURE "RI_FKey_noaction_upd"('plugin_frs_frgroups_groupid', 'plugin_frs_fr_groups', 'groups', 'UNSPECIFIED', 'group_id', 'group_id');


INSERT INTO plugin_frs_fr_states (stateid, name) VALUES (1,'active');
INSERT INTO plugin_frs_fr_states (stateid, name) VALUES (2,'deleted');
INSERT INTO plugin_frs_fr_states (stateid, name) VALUES (3,'pending');
INSERT INTO plugin_frs_fr_states (stateid, name) VALUES (4,'hidden');
INSERT INTO plugin_frs_fr_states (stateid, name) VALUES (5,'private');



SELECT pg_catalog.setval('plugin_frs_fr_data_pk_seq', (select coalesce(max(frid)+1, 1) from plugin_frs_fr_data), false);
SELECT pg_catalog.setval('plugin_frs_fr_groups_pk_seq', (select coalesce(max(fr_group)+1, 1) from plugin_frs_fr_groups), false);
SELECT pg_catalog.setval('plugin_frs_fr_states_pk_seq', (select coalesce(max(stateid)+1, 1) from plugin_frs_fr_states), false);


CREATE INDEX plugin_frs_fr_groups_group ON plugin_frs_fr_groups USING btree (group_id);

CREATE INDEX plugin_frs_frgroups_parentfrgroup ON plugin_frs_fr_groups USING btree (parent_fr_group);

CREATE INDEX plugin_frs_fr_authorization_index ON plugin_frs_fr_authorization USING btree(fr_group,role_id);


 
