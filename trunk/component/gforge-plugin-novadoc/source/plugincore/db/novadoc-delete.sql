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
 * Delete all documents/groups in the database for all projects !!!!!
 *
 * @version   $Id: docs-delete-all.sql,v 1.2 2006/11/02 19:02:28 pascal Exp $
 */
drop SEQUENCE plugin_docs_doc_data_pk_seq;

drop TABLE plugin_docs_doc_status_table cascade;

drop TABLE plugin_docs_doc_data  cascade;
drop SEQUENCE plugin_docs_doc_groups_pk_seq;

drop TABLE plugin_docs_doc_authorization;

drop TABLE plugin_docs_doc_groups cascade;

drop SEQUENCE plugin_docs_doc_states_pk_seq;

drop TABLE plugin_docs_doc_states cascade;

drop TABLE plugin_docs_doc_chrono;


