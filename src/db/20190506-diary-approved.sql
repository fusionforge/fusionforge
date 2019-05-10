ALTER TABLE user_diary ADD COLUMN is_approved integer DEFAULT 0 NOT NULL;
UPDATE user_diary SET is_approved = 0;
