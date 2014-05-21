-- 'forum' confusingly stores forum messages, not forums.
-- 'forum_saved_place' references forum, not forum messages.
-- The following fixes errors such as:
-- Forum::savePlace() : ERROR: insert or update on table "forum_saved_place" violates foreign key constraint "forum_saved_place_forum_id_fkey" DÉTAIL : Key (forum_id)=(10462) is not present in table "forum".
ALTER TABLE forum_saved_place DROP CONSTRAINT forum_saved_place_forum_id_fkey;
ALTER TABLE forum_saved_place ADD FOREIGN KEY (forum_id) REFERENCES forum_group_list (group_forum_id) ON DELETE CASCADE ON UPDATE CASCADE;
