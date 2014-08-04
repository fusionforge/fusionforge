<?php

// Where the attachments are located
$forumml_dir = forge_get_config("data_path")."/forumml";

// Where the temporary mail file is stored
$forumml_tmp = "/var/spool/forumml";

// Where Mailman archives are stored
$forumml_arch = "/var/lib/mailman/archives";

// You can exclude some lists from forumml archival (comma separted values)
$forumml_excluded_lists = "";

?>
