<?

require_once ("common/novaforge/log.php");
require_once ("plugins/novafrs/include/FileFactory.class.php");
require_once ("plugins/novafrs/include/FileGroupAuth.class.php");
require_once ("plugins/novafrs/include/FileGroupFactory.class.php");
require_once ("plugins/novafrs/include/utils.php");

class novafrsPublisher
{

	function novafrsPublisher ()
	{
	}

	function getXml ($group_id, $role_id, &$xml)
	{
		$ok = false;
		$group = group_get_object ($group_id);
		if ((isset ($group) == false) || (is_object ($group) == false) || ($group->isError () == true))
		{
			log_error ("Error while getting group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
		}
		else
		{
			$file_config = FileConfig::getInstance ();
			if ((isset ($file_config) == false) || (is_object ($file_config) == false))
			{
				log_error ("Error while creating file config", __FILE__, __FUNCTION__, __CLASS__);
			}
			else
			{
				$file_group_factory = new FileGroupFactory ($group);
				if ((isset ($file_group_factory) == false) || (is_object ($file_group_factory) == false) || ($file_group_factory->isError () == true))
				{
					log_error ("Error while getting file group factory for group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
				}
				else
				{
					$file_factory = new FileFactory ($group);
					if ((isset ($file_factory) == false) || (is_object ($file_factory) == false) || ($file_factory->isError () == true))
					{
						log_error ("Error while getting file factory for group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
					}
					else
					{
						$array_filegroup_role = FileGroupAuth::getAllAuth ($group_id);
						if ($array_filegroup_role === false)
						{
							log_error ("Error while getting roles for file groups of group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
						}
						else
						{
							if ($this->getFileGroupXml ($group,
							                            $file_config,
							                            $file_factory,
							                            $array_filegroup_role,
							                            $role_id,
							                            $file_group_factory->getNested (),
							                            0,
							                            $xml) == false)
							{
								log_error ("Error while getting XML for file groups of group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
							}
							else
							{
								$ok = true;
							}
						}
					}
				}
			}
		}
		return $ok;
	}

	function getFileName ($group_id, $file_id, &$filename)
	{
		$ok = false;
		$file_config = FileConfig::getInstance ();
		if ((isset ($file_config) == false) || (is_object ($file_config) == false))
		{
			log_error ("Error while creating file config", __FILE__, __FUNCTION__, __CLASS__);
		}
		else
		{
			$group = group_get_object ($group_id);
			if ((isset ($group) == false) || (is_object ($group) == false))
			{
				log_error ("Error while creating group '" . $group_id . "'", __FILE__, __FUNCTION__, __CLASS__);
			}
			else
			{
				$file = new File ($group, $file_id);
				if ((isset ($file) == false) || (is_object ($file) == false) || ($file->isError () == true))
				{
					log_error ("Error while creating file '" . $file_id . "' of group '" . $group_id . "'", __FILE__, __FUNCTION__, __CLASS__);
				}
				else
				{
					$file_group_frs = new FileGroupFrs ($group, $file->getFrGroupID ());
					if ((isset ($file_group_frs) == false) || (is_object ($file_group_frs) == false))
					{
						log_error ("Error while creating file group '" . $file->getFrGroupID () . "' of group '" . $group_id . "'", __FILE__, __FUNCTION__, __CLASS__);
					}
					else
					{
						$ok = true;
						$filename = $file_config->sys_novafrs_path;
						if ($filename [strlen ($filename) - 1] != "/")
						{
							$filename .= "/";
						}
						$filename .= $group->getUnixName () . "/" . $file_group_frs->getPath () . "/" . novafrs_unixString ($file->getFileName ());
					}
				}
			}
		}
		return $ok;
	}

	function getFileGroupXml (&$group,
	                          &$file_config,
	                          &$file_factory,
	                          &$array_filegroup_role,
	                          $role_id,
	                          &$array_filegroup,
	                          $filegroup_id,
	                          &$xml)
	{
		$ok = true;
		if ((is_array ($array_filegroup) == true)
		&&  (array_key_exists ($filegroup_id, $array_filegroup) == true))
		{
			$keys = array_keys ($array_filegroup [$filegroup_id]);
			$i = 0;
			while (($i < count ($keys)) && ($ok == true))
			{
				$filegroup = $array_filegroup [$filegroup_id] [$i];
				if ($array_filegroup_role [$filegroup->getID ()] [$role_id] ["auth"] > 1)
				{
					$xml .= "<folder id=\"" . $filegroup->getID () . "\" name=\"" . $filegroup->getName () . "\">";
					$file_factory->setFrGroupID ($filegroup->getID ());
					$array_files = $file_factory->getFiles ();
					if ((isset ($array_files) == true) && (is_array ($array_files) == true))
					{
						$j = 0;
						while (($j < count ($array_files)) && ($ok == true))
						{
							$filename = $file_config->sys_novafrs_path;
							if ($filename [strlen ($filename) - 1] != "/")
							{
								$filename .= "/";
							}
							$filename .= $group->getUnixName () . "/" . $filegroup->getPath () . "/" . novafrs_unixString ($array_files [$j]->getFileName ());
							$md5 = md5_file ($filename);
							if (($md5 === false) || (strlen ($md5) == 0))
							{
								$ok = false;
								log_error ("Error while calculating MD5 sum of file '" . $filename . "'", __FILE__, __FUNCTION__, __CLASS__);
							}
							else
							{
								$xml .= "<file id=\""
								     . $array_files [$j]->getID ()
								     . "\" filename=\""
								     . novafrs_unixString ($array_files [$j]->getFileName ())
								     . "\" md5=\""
								     . $md5
								     . "\" title=\""
								     . $array_files [$j]->getName ()
								     . "\"";
								if ((is_array ($array_files [$j]->data_array) == true)
								&&  (count ($array_files [$j]->data_array) > 0))
								{
$xml .= ">";
									foreach ($array_files [$j]->data_array as $key => $value)
									{
										$xml .= "<property name=\"" . $key . "\" value=\"" . $value . "\"/>";
									}
									$xml .= "</file>";
								}
								else
								{
									$xml .= "/>";
								}
							}
							$j++;
						}
					}
					if ($ok == true)
					{
						$ok = $this->getFileGroupXml ($group,
						                              $file_config,
						                              $file_factory,
						                              $array_filegroup_role,
						                              $role_id,
						                              $array_filegroup,
						                              $filegroup->getID (),
						                              $xml);
					}
					$xml .= "</folder>";
				}
				$i++;
			}
		}
		return $ok;
	}
}

?>
