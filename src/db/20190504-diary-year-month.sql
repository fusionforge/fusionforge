ALTER TABLE user_diary ADD COLUMN year integer DEFAULT 0 NOT NULL;
ALTER TABLE user_diary ADD COLUMN month integer DEFAULT 0 NOT NULL;

UPDATE user_diary SET year = extract('year' from to_timestamp(date_posted));
UPDATE user_diary SET month = extract('month' from to_timestamp(date_posted));
