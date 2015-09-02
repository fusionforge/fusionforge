UPDATE trove_cat SET fullname=regexp_replace(fullname, '^Linux', 'GNU/Linux') WHERE trove_cat_id=201;
UPDATE trove_cat SET description=regexp_replace(description, 'of Linux', 'of GNU/Linux') WHERE trove_cat_id=201;
UPDATE trove_cat SET fullpath=regexp_replace(fullpath, ':: Linux', ':: GNU/Linux') WHERE trove_cat_id=201;
UPDATE trove_cat SET shortname=regexp_replace(shortname, '^linux', 'gnu-linux') WHERE trove_cat_id=201;

UPDATE trove_cat SET fullname=replace(fullname, 'GNU Hurd', 'GNU/Hurd') WHERE trove_cat_id=240;
UPDATE trove_cat SET description=replace(description, 'GNU Hurd', 'GNU/Hurd') WHERE trove_cat_id=240;
UPDATE trove_cat SET fullpath=replace(fullpath, 'GNU Hurd', 'GNU/Hurd') WHERE trove_cat_id=240;
UPDATE trove_cat SET shortname=replace(shortname, 'gnuhurd', 'gnu-hurd') WHERE trove_cat_id=240;
