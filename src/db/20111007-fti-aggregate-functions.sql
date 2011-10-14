CREATE OR REPLACE FUNCTION ff_tsvector_add (v1 tsvector, v2 tsvector)
RETURNS tsvector
AS $$
BEGIN
  RETURN v1 || v2 ;
END;
$$ LANGUAGE plpgsql
IMMUTABLE
RETURNS NULL ON NULL INPUT;

CREATE AGGREGATE ff_tsvector_agg (
       basetype = tsvector,
       sfunc = ff_tsvector_add,
       stype = tsvector
);
