CREATE OR REPLACE FUNCTION ff_string_add (t1 text, t2 text)
RETURNS text
AS $$
BEGIN
  RETURN t1 || ' ioM0Thu6_fieldseparator_kaeph9Ee ' || t2 ;
END;
$$ LANGUAGE plpgsql
IMMUTABLE
RETURNS NULL ON NULL INPUT;

CREATE AGGREGATE ff_string_agg (
       basetype = text,
       sfunc = ff_string_add,
       stype = text
);
