--
-- PROJECT STATS 
--
DROP TABLE "stats_project";
CREATE TABLE "stats_project" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "file_releases" integer DEFAULT 0,
        "msg_posted" integer DEFAULT 0,
        "msg_uniq_auth" integer DEFAULT 0,
        "bugs_opened" integer DEFAULT 0,
        "bugs_closed" integer DEFAULT 0,
        "support_opened" integer DEFAULT 0,
        "support_closed" integer DEFAULT 0,
        "patches_opened" integer DEFAULT 0,
        "patches_closed" integer DEFAULT 0,
        "artifacts_opened" integer DEFAULT 0,
        "artifacts_closed" integer DEFAULT 0,
        "tasks_opened" integer DEFAULT 0,
        "tasks_closed" integer DEFAULT 0,
        "help_requests" integer DEFAULT 0
);
CREATE UNIQUE INDEX "statsproject_month_day_group" on "stats_project" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

---
--- PROJECT METRIC
---
DROP TABLE "stats_project_metric";
CREATE TABLE "stats_project_metric" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "ranking" integer DEFAULT 0 NOT NULL,
        "percentile" double precision DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statsprojectmetric_month_day_gr" on "stats_project_metric" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );

--
-- PROJECT DEVELOPPERS
--
DROP TABLE "stats_project_developers";

CREATE TABLE "stats_project_developers" (
        "month" integer DEFAULT 0 NOT NULL,
        "day" integer DEFAULT 0 NOT NULL,
        "group_id" integer DEFAULT 0 NOT NULL,
        "developers" integer DEFAULT 0 NOT NULL
);
CREATE UNIQUE INDEX "statsprojectdev_month_day_group" on "stats_project_developers" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "group_id" "int4_ops" );
