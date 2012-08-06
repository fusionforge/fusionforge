ALTER TABLE "artifact_query" ADD COLUMN "query_options" text;
UPDATE "artifact_query" SET query_options='';
ALTER TABLE "artifact_query" ALTER COLUMN "query_options" SET DEFAULT '';
ALTER TABLE "artifact_query" ALTER COLUMN "query_options" SET NOT NULL;
