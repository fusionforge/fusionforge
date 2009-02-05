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

/*
 * Create the tables of the NovaForge Publisher plugin
 */

CREATE SEQUENCE plugin_novapub_site_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_novapub_site
(
    id integer DEFAULT nextval('plugin_novapub_site_pk_seq'::text) UNIQUE NOT NULL,
    name varchar(128) NOT NULL,
    value text NOT NULL
);

CREATE SEQUENCE plugin_novapub_project_pk_seq
    START 1
    INCREMENT 1
    MAXVALUE 2147483647
    MINVALUE 1
    CACHE 1;

CREATE TABLE plugin_novapub_project
(
    id integer DEFAULT nextval('plugin_novapub_project_pk_seq'::text) UNIQUE NOT NULL,
    name varchar(128) NOT NULL,
    group_id integer DEFAULT 0 NOT NULL,
    role_id integer DEFAULT 0 NOT NULL,
    url text NOT NULL
);
