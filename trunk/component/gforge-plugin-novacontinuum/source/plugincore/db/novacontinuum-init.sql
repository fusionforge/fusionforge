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

CREATE TABLE plugin_novacontinuum_version(
    version        character varying(150) not null default 'novacontinuum-1.0' 
);

INSERT INTO plugin_novacontinuum_version (version) VALUES ('novacontinuum-1.0');

CREATE SEQUENCE plugin_novacontinuum_instance_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;
 
CREATE TABLE plugin_novacontinuum_instances
(
    id integer DEFAULT nextval('plugin_novacontinuum_instance_pk_seq') UNIQUE NOT NULL,
    name varchar(128) NOT NULL,
    url text NOT NULL,
    userName varchar(128) NOT NULL,
    pwd varchar(128) NOT NULL,
    maxUse integer DEFAULT 0 NOT NULL,
    isEnabled integer DEFAULT 1 NOT NULL,
    proxyId integer,
    groupId integer
);

CREATE SEQUENCE plugin_novacontinuum_http_proxy_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_novacontinuum_http_proxy
(
    id integer DEFAULT nextval('plugin_novacontinuum_http_proxy_pk_seq') UNIQUE NOT NULL,
    name varchar(128) NOT NULL,
    host text NOT NULL,
    port integer NOT NULL,
    userName varchar(128) NOT NULL,
    pwd varchar(128) NOT NULL
    
);

CREATE TABLE plugin_novacontinuum_configuration
(
		keyName varchar(128) NOT NULL,
    configValue text NOT NULL
);

CREATE TABLE plugin_novacontinuum_instances_projects
(
		instanceid integer NOT NULL,
    groupid integer NOT NULL,
    continuumProjectGroupId integer NOT NULL
);

CREATE TABLE plugin_novacontinuum_group_roles
(
		groupid integer NOT NULL,
    roleid integer NOT NULL,
    rolename varchar(255) NOT NULL
);

CREATE SEQUENCE plugin_novacontinuum_projects_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_novacontinuum_projects
(
    id integer DEFAULT nextval('plugin_novacontinuum_projects_pk_seq') UNIQUE NOT NULL,
    name varchar(128) NOT NULL,
    url text NOT NULL,
    userName varchar(128) NOT NULL,
    pwd varchar(128) NOT NULL,
    groupid integer NOT NULL
);

CREATE TABLE plugin_novacontinuum_continuum_projects
(
	projectId integer NOT NULL,
	continuumProjectId integer NOT NULL
);