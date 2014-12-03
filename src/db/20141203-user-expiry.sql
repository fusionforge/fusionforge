-- ALTER TABLE ADD COLUMN IF NOT EXISTS for PostgreSQL

DO $$
	BEGIN
		BEGIN
			ALTER TABLE users
			    ADD COLUMN expire_date INTEGER NOT NULL DEFAULT 0;
		EXCEPTION
			WHEN duplicate_column THEN RAISE NOTICE 'column expire_date already added to table users';
		END;
	END;
$$;
