DROP INDEX "debian_statscvsgroup_month_day_group";
DROP TABLE "debian_stats_cvs_group";

CREATE TABLE "debian_stats_cvs_group" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "checkouts" integer DEFAULT 0 NOT NULL,
        "commits" integer DEFAULT 0 NOT NULL,
        "adds" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "debian_statscvsgroup_month_day_group" on "debian_stats_cvs_group" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

