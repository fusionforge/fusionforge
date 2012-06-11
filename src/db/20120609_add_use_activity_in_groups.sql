ALTER TABLE "groups" ADD COLUMN "use_activity" integer;
UPDATE "groups" SET use_activity=1;
ALTER TABLE "groups" ALTER COLUMN "use_activity" SET NOT NULL;
ALTER TABLE "groups" ALTER COLUMN "use_activity" SET DEFAULT 1;
