CREATE SEQUENCE "plugin_globalsearch_assoc_status_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE TABLE "plugin_globalsearch_assoc_status" (
  "status_id" integer DEFAULT nextval('plugin_globalsearch_assoc_status_pk_seq'::text) NOT NULL,
  "status_name" text DEFAULT '' NOT NULL,
  CONSTRAINT "plugin_globalsearch_assoc_status_pkey" PRIMARY KEY ("status_id")
);

INSERT INTO plugin_globalsearch_assoc_status (status_name)
VALUES ('New') ;

INSERT INTO plugin_globalsearch_assoc_status (status_name)
VALUES ('Ok') ;

INSERT INTO plugin_globalsearch_assoc_status (status_name)
VALUES ('Fail') ;

INSERT INTO plugin_globalsearch_assoc_status (status_name)
VALUES ('Unparsable') ;

SELECT setval ('"plugin_globalsearch_assoc_status_pk_seq"', 4, false);

CREATE SEQUENCE "plugin_globalsearch_assoc_site_pk_seq" start 1 increment 1 maxvalue 2147483647 minvalue 1 cache 1;
CREATE TABLE "plugin_globalsearch_assoc_site" (
  "assoc_site_id" integer DEFAULT nextval('plugin_globalsearch_assoc_site_pk_seq'::text) NOT NULL,
  "title" text,
  "link" text,
  "onlysw" boolean DEFAULT 't'::bool,
  "enabled" boolean DEFAULT 't'::bool,
  "status_id" integer DEFAULT '1' NOT NULL,
  "rank" integer DEFAULT '1' NOT NULL,
  CONSTRAINT "plugin_globalsearch_assoc_site_pkey" PRIMARY KEY ("assoc_site_id"),
  CONSTRAINT "plugin_globalsearch_assoc_site_status_fkey" FOREIGN KEY (status_id) REFERENCES plugin_globalsearch_assoc_status(status_id)
);

INSERT INTO plugin_globalsearch_assoc_site (title,link,onlysw,enabled,status_id,rank)
VALUES ('FusionForge.org', 'https://fusionforge.org/', 't', 't', 1, 1) ;
INSERT INTO plugin_globalsearch_assoc_site (title,link,onlysw,enabled,status_id,rank)
VALUES ('Alioth', 'https://alioth.debian.org/', 't', 't', 1, 2) ;

CREATE TABLE "plugin_globalsearch_assoc_site_project" (
  "assoc_site_id" integer DEFAULT '0' NOT NULL,
  "project_title" text,
  "project_link" text,
  "project_description" text,
  CONSTRAINT "plugin_globalsearch_assoc_site_project_site_fkey" FOREIGN KEY (assoc_site_id) REFERENCES plugin_globalsearch_assoc_site (assoc_site_id)
);

CREATE INDEX plugin_globalsearch_assoc_title_idx ON plugin_globalsearch_assoc_site USING btree (title);
CREATE INDEX plugin_globalsearch_assoc_status_idx ON plugin_globalsearch_assoc_site USING btree (status_id);
CREATE INDEX plugin_globalsearch_assoc_enabled_idx ON plugin_globalsearch_assoc_site USING btree (enabled);
CREATE INDEX plugin_globalsearch_assoc_onlysw_idx ON plugin_globalsearch_assoc_site USING btree (onlysw);
CREATE INDEX plugin_globalsearch_assoc_rank_idx ON plugin_globalsearch_assoc_site USING btree (rank);
CREATE INDEX plugin_globalsearch_assocproj_asid_idx ON plugin_globalsearch_assoc_site_project USING btree (assoc_site_id);
CREATE INDEX plugin_globalsearch_assocproj_titl_idx ON plugin_globalsearch_assoc_site_project USING btree (project_title);
CREATE INDEX plugin_globalsearch_assocproj_link_idx ON plugin_globalsearch_assoc_site_project USING btree (project_link);
CREATE INDEX plugin_globalsearch_assocproj_desc_idx ON plugin_globalsearch_assoc_site_project USING btree (project_description);
