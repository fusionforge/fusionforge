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
-- Suppression of the table containing the version of the plugin
drop TABLE plugin_novacontinuum_version;

DROP SEQUENCE plugin_novacontinuum_instance_pk_seq;

DROP TABLE plugin_novacontinuum_instances cascade;

DROP SEQUENCE plugin_novacontinuum_http_proxy_pk_seq;

DROP TABLE plugin_novacontinuum_http_proxy cascade;

DROP TABLE plugin_novacontinuum_configuration cascade;

DROP TABLE plugin_novacontinuum_instances_projects cascade;

DROP TABLE plugin_novacontinuum_group_roles cascade;

DROP SEQUENCE plugin_novacontinuum_projects_pk_seq;

DROP TABLE plugin_novacontinuum_projects cascade;

DROP TABLE plugin_novacontinuum_continuum_projects cascade;
