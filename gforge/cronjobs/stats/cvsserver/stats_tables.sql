--
-- CVS
--
--
-- Stats by group
--
DROP TABLE "stats_cvs_group";

CREATE TABLE "stats_cvs_group" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "checkouts" integer DEFAULT 0 NOT NULL,
        "commits" integer DEFAULT 0 NOT NULL,
        "adds" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statscvsgroup_month_day_group" on "stats_cvs_group" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );
--
-- Stats by user
--
DROP TABLE "stats_cvs_user";
CREATE TABLE "stats_cvs_user" (
	"month" integer DEFAULT 0 NOT NULL,
	"day" integer DEFAULT 0 NOT NULL,
	"group_id" integer DEFAULT 0 NOT NULL,
	"user_id" integer DEFAULT 0 NOT NULL,
	"checkouts" integer DEFAULT 0 NOT NULL,
	"commits" integer DEFAULT 0 NOT NULL,
	"adds" integer DEFAULT 0 NOT NULL
);

