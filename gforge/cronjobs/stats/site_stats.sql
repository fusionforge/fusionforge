---
---  
---
DROP TABLE "stats_site_pages_by_day";
CREATE TABLE "stats_site_pages_by_day" (
        "month" integer,
        "day" integer,
        "site_page_views" integer
);
CREATE  INDEX "statssitepagesbyday_month_day" on "stats_site_pages_by_day" 
	using btree ( "month" "int4_ops", "day" "int4_ops" );

---
---  
---
DROP TABLE "stats_agg_logo_by_group";
CREATE TABLE "stats_agg_logo_by_group" (
        "month" integer,
        "day" integer,
        "group_id" integer,
        "count" integer
);
CREATE UNIQUE INDEX "statslogobygroup_month_day_grou" on "stats_agg_logo_by_group" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

---
---  
---
DROP TABLE "stats_agg_site_by_group";
CREATE TABLE "stats_agg_site_by_group" (
        "month" integer,
        "day" integer,
        "group_id" integer,
        "count" integer
);
CREATE UNIQUE INDEX "statssitebygroup_month_day_grou" on "stats_agg_site_by_group" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );
