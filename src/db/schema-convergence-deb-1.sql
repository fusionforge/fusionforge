ALTER TABLE ONLY doc_data ADD CONSTRAINT docdata_languageid_fk FOREIGN KEY (language_id) REFERENCES supported_languages(language_id) MATCH FULL;
ALTER TABLE ONLY forum ADD CONSTRAINT forum_posted_by_fk FOREIGN KEY (posted_by) REFERENCES users(user_id) MATCH FULL;
