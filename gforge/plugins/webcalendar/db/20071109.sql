-- This script must be used only if you have activated the plugin webcalendar before 
-- the creation of this script in the repository of gforge.


UPDATE role_setting SET value = '6' WHERE section_name = 'webcal' AND value = '2';
UPDATE role_setting SET value = '2' WHERE section_name = 'webcal' AND value = '1';
UPDATE role_setting SET value = '1' WHERE section_name = 'webcal' AND value = '6';
