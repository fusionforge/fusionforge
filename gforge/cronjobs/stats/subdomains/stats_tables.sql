---
--- SUBDOMAIN
---
DROP TABLE "stats_subd_pages";
CREATE TABLE "stats_subd_pages" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "pages" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statssubdpages_month_day_group" on "stats_subd_pages" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

