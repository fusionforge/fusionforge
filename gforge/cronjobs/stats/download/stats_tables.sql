--
-- DOWNLOAD
--
DROP TABLE "frs_dlstats_file_agg";

CREATE TABLE "frs_dlstats_file_agg" (
        "month" integer,
        "day" integer,
        "file_id" integer,
        "downloads" integer
);
CREATE UNIQUE INDEX "frsdlfileagg_month_day_file" on "frs_dlstats_file_agg" 
	using btree ( "month" "int4_ops", "day" "int4_ops", "file_id" "int4_ops" );

