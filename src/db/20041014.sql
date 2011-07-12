-- Insert ultralite, only if it doesn't already exists
INSERT INTO themes (dirname,fullname,enabled) (SELECT 'ultralite','Ultra-Lite Text-only Theme',true WHERE (SELECT COUNT(*) FROM themes WHERE dirname ='ultralite' ) = 0) ;
UPDATE themes SET fullname = 'Ultra-Lite Text-only Theme' where dirname = 'ultralite';

