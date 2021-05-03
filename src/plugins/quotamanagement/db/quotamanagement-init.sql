-- **
-- * Quota management support.
-- *
-- * Copyright 2005 (c) Sogeti-Transiciel Technologies
-- *
-- * @author Olivier Fourdan ofourdan@mail.transiciel.com
-- * @date 2005-11-15
-- *
-- * This file is released under the GNU GPL license.
-- *
-- **
ALTER TABLE groups ADD COLUMN quota_soft int;
ALTER TABLE groups ALTER COLUMN quota_soft SET DEFAULT 0;
ALTER TABLE groups ADD COLUMN quota_hard int;
ALTER TABLE groups ALTER COLUMN quota_hard SET DEFAULT 0;
UPDATE groups SET quota_soft=0;
UPDATE groups SET quota_hard=0;
