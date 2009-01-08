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
--
-- This script creates the views for system authentication through NSS
--

--
-- Drop previous views
--
DROP VIEW nss_passwd,nss_groups,nss_usergroups;

--
-- Create view nss_passwd
--
CREATE VIEW nss_passwd AS
    SELECT
	user_name						AS name,
	unix_pw							AS passwd,
        unix_uid						AS uid,
        unix_gid						AS gid,
        realname						AS gecos,
	('%LOCALSTATEDIR%/lib/%NAME%/home/users/'||user_name)	AS homedir,
        shell							AS shell
    FROM
        users
    WHERE
        status='A'::bpchar
    AND
        unix_status='A'::bpchar;

--
-- Create view nss_groups
--
CREATE VIEW nss_groups AS
    SELECT
        user_name		AS name,
        unix_gid		AS gid
    FROM
        users
    WHERE
        status='A'::bpchar
    AND
        unix_status='A'::bpchar
UNION
    SELECT
        unix_group_name		AS name,
        group_id+10000		AS gid
    FROM
        groups
    WHERE
        status='A'::bpchar;

--
-- Create view nss_usergroups
--
CREATE VIEW nss_usergroups AS
    SELECT
	user_name			AS user_name,
        unix_gid			AS gid
    FROM
        users
    WHERE
        status='A'::bpchar
    AND
        unix_status='A'::bpchar
UNION
    SELECT
	users.user_name			AS user_name,
	user_group.group_id+10000	AS gid
    FROM
        user_group,users,groups
    WHERE
        users.user_id=user_group.user_id
    AND
        groups.group_id=user_group.group_id
    AND
        groups.status='A'::bpchar
    AND
        users.status='A'::bpchar
    AND
        users.unix_status='A'::bpchar
UNION
    SELECT
        'apache'			AS user_name,
        groups.group_id+10000		AS gid
    FROM
        groups
    WHERE
        groups.status='A'::bpchar;
