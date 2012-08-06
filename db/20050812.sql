CREATE TABLE form_keys (
    key_id serial NOT NULL,
    "key" text NOT NULL,
    creation_date integer NOT NULL,
    is_used integer DEFAULT 0 NOT NULL
);


ALTER TABLE ONLY form_keys
    ADD CONSTRAINT form_keys_pkey PRIMARY KEY (key_id);

ALTER TABLE ONLY form_keys
    ADD CONSTRAINT "key" UNIQUE ("key");


