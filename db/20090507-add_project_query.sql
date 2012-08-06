ALTER TABLE "artifact_query" ADD COLUMN "query_type" integer;
UPDATE "artifact_query" SET query_type=0;
ALTER TABLE "artifact_query" ALTER COLUMN "query_type" SET DEFAULT 0;
ALTER TABLE "artifact_query" ALTER COLUMN "query_type" SET NOT NULL;
