<?

require_once ("common/novaforge/log.php");
require_once ("plugins/novadoc/include/DocumentFactory.class.php");
require_once ("plugins/novadoc/include/DocumentGroupAuth.class.php");
require_once ("plugins/novadoc/include/DocumentGroupFactory.class.php");
require_once ("plugins/novadoc/include/utils.php");

class novadocPublisher
{

	function novadocPublisher ()
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
			$document_config = DocumentConfig::getInstance ();
			if ((isset ($document_config) == false) || (is_object ($document_config) == false))
			{
				log_error ("Error while creating document config", __FILE__, __FUNCTION__, __CLASS__);
			}
			else
			{
				$document_group_factory = new DocumentGroupFactory ($group);
				if ((isset ($document_group_factory) == false) || (is_object ($document_group_factory) == false) || ($document_group_factory->isError () == true))
				{
					log_error ("Error while getting document group factory for group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
				}
				else
				{
					$document_factory = new DocumentFactory ($group);
					if ((isset ($document_factory) == false) || (is_object ($document_factory) == false) || ($document_factory->isError () == true))
					{
						log_error ("Error while getting document factory for group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
					}
					else
					{
						$array_docgroup_role = DocumentGroupAuth::getAllAuth ($group_id);
						if ($array_docgroup_role === false)
					{
							log_error ("Error while getting roles for document groups of group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
						}
						else
						{
							if ($this->getDocGroupXml ($group,
							                           $document_config,
							                           $document_factory,
							                           $array_docgroup_role,
							                           $role_id,
							                           $document_group_factory->getNested (),
							                           0,
							                           $xml) == false)
							{
								log_error ("Error while getting XML for document groups of group " . $group_id, __FILE__, __FUNCTION__, __CLASS__);
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
		$document_config = DocumentConfig::getInstance ();
		if ((isset ($document_config) == false) || (is_object ($document_config) == false))
		{
			log_error ("Error while creating document config", __FILE__, __FUNCTION__, __CLASS__);
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
				$document = new Document ($group, $file_id);
				if ((isset ($document) == false) || (is_object ($document) == false) || ($document->isError () == true))
				{
					log_error ("Error while creating document '" . $file_id . "' of group '" . $group_id . "'", __FILE__, __FUNCTION__, __CLASS__);
				}
				else
				{
					$document_group_docs = new DocumentGroupDocs ($group, $document->getDocGroupID ());
					if ((isset ($document_group_docs) == false) || (is_object ($document_group_docs) == false))
					{
						log_error ("Error while creating document group '" . $document->getDocGroupID () . "' of group '" . $group_id . "'", __FILE__, __FUNCTION__, __CLASS__);
					}
					else
					{
						$ok = true;
						$filename = $document_config->sys_novadoc_path;
						if ($filename [strlen ($filename) - 1] != "/")
						{
							$filename .= "/";
						}
						$filename .= $group->getUnixName () . "/" . $document_group_docs->getPath () . "/" . novadoc_unixString ($document->getFileName ());
					}
				}
			}
		}
		return $ok;
	}

	function getDocGroupXml (&$group,
	                         &$document_config,
	                         &$document_factory,
	                         &$array_docgroup_role,
	                         $role_id,
	                         &$array_docgroup,
	                         $docgroup_id,
	                         &$xml)
	{
		$ok = true;
		if ((is_array ($array_docgroup) == true)
		&&  (array_key_exists ($docgroup_id, $array_docgroup) == true))
		{
			$keys = array_keys ($array_docgroup [$docgroup_id]);
			$i = 0;
			while (($i < count ($keys)) && ($ok == true))
			{
				$docgroup = $array_docgroup [$docgroup_id] [$i];
				if ($array_docgroup_role [$docgroup->getID ()] [$role_id] ["auth"] > 1)
				{
					$xml .= "<folder id=\"" . $docgroup->getID () . "\" name=\"" . $docgroup->getName () . "\">";
					$document_factory->setDocGroupID ($docgroup->getID ());
					$array_documents = $document_factory->getDocuments ();
					if ((isset ($array_documents) == true) && (is_array ($array_documents) == true))
					{
						$j = 0;
						while (($j < count ($array_documents)) && ($ok == true))
						{
							$filename = $document_config->sys_novadoc_path;
							if ($filename [strlen ($filename) - 1] != "/")
							{
								$filename .= "/";
							}
							$filename .= $group->getUnixName () . "/" . $docgroup->getPath () . "/" . novadoc_unixString ($array_documents [$j]->getFileName ());
							$md5 = md5_file ($filename);
							if (($md5 === false) || (strlen ($md5) == 0))
							{
								$ok = false;
								 log_error ("Error while calculating MD5 sum of file '" . $filename . "'", __FILE__, __FUNCTION__, __CLASS__);
							}
							else
							{
								$xml .= "<file id=\""
								     . $array_documents [$j]->getID ()
								     . "\" filename=\""
								     . novadoc_unixString ($array_documents [$j]->getFileName ())
								     . "\" md5=\""
								     . $md5
								     . "\" title=\""
								     . $array_documents [$j]->getName ()
								     . "\"";
								if ((is_array ($array_documents [$j]->data_array) == true)
								&&  (count ($array_documents [$j]->data_array) > 0))
								{
									$xml .= ">";
									foreach ($array_documents [$j]->data_array as $key => $value)
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
						$ok = $this->getDocGroupXml ($group,
						                             $document_config,
						                             $document_factory,
						                             $array_docgroup_role,
						                             $role_id,
						                             $array_docgroup,
					        	                     $docgroup->getID (),
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
