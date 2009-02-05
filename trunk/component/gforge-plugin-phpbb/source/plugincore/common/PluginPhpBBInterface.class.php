<?php

require_once ('plugins/phpbb/common/ResponseParser.class');
/**
 * Static class : Let you call a phpBB instance remotely
 *
 * @author Panneer CANABADY <panneerselvam.canabady@bull.net>
 * @version 1.0
 * @package PluginPhpBB
 */
class PluginPhpBBInterface {


    /**
     * Subscribes a user into PhpBB
     *
     * @param String $username user name.
     * @param String $email email adress
     * @param String $password  MD5 encoded password
     * @param String $language the user language ex: 'en', 'fr'
     * @param float $timezone shifted time related to UTC base time.
     *                           examples : paris : +1 / kaboul +4.5 / hawaii -10 .
     *
     * @return integer the new user id or zero if an error occured
     */
    function createUser($url,$username,$email,$password,$admin= false,$language = 'en' ,$timezone = 1 ) {
        syslog (LOG_INFO, ">> PluginPhpBBInterface::createUser($url,$username,$email,$password,$language ,$timezone )");

        $returned=-1;

        $xml_in = "<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                         <CREATE_USER NAME="{$username}"
                                      EMAIL="{$email}" 
                                      PASSWORD="{$password}"
                                      LANGUAGE="{$language}" 
                                      TIMEZONE="{$timezone}"
                         />

                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['USER']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::createUser success response : $returned");

            }
        }

        syslog (LOG_INFO, "<<  PluginPhpBBInterface::createUser return $returned");
        return ($returned);
    }

    /**
     * Creates a group into PhpBB
     *
     * @param string groupName group name
     *
     * @return the new group id or zero if an error occured
     */
    function createGroup($url,$groupName,$groupDesc = '' ) {
        syslog (LOG_INFO, ">> PluginPhpBBInterface::createGroup($url,$groupName,$groupDesc )");

        $returned=-1;

        $xml_in = "<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <CREATE_GROUP NAME="{$groupName}"  />
                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['GROUP']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::createGroup success response : $returned");
            }
        }



        syslog (LOG_INFO, "<<  PluginPhpBBInterface::createGroup return $returned");
        return ($returned);
    }

    /**
     * Creates category into PhpBB.
     *
     * @param int $parentId
     * @param string $newCategory new category name
     *
     * @return the new category id or zero if an error occured
     */
    function createCategory($url,$parentId,$newCategory) {
        syslog (LOG_INFO, ">> PluginPhpBBInterface::createCategory($parentId,$newCategory)");

        $returned=-1;

        $xml_in = "<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <CREATE_CATEGORY PARENT_ID="{$parentId}"
                                             NAME="{$newCategory}"  />
                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->categories[0]['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::createCategory  success response : $returned ");
            }
        }



        syslog (LOG_INFO, "<<  PluginPhpBBInterface::createCategory return $returned");
        return ($returned);
    }

    /**
     * Renames a category
     *
     * @param integer $categoryId the id of the category
     * @param string $newName the new name of the categorie
     */
    function renameCategory($url,$categoryId,$newName){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::renameCategory($url,$categoryId,$newName)");
        $returned=-1;

        $xml_in = "<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <RENAME_CATEGORY ID="{$categoryId}"
                                             NAME="{$newName}"  />
                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->categories[0]['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::renameCategory  success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::renameCategory return $returned");
        return $returned;
    }

    /**
     * Deletes a category
     *
     * @param integer $categoryId the id of the category
     * @return positive number if succeed
     */
    function deleteCategory($url,$categoryId) {
        syslog (LOG_INFO, ">> PluginPhpBBInterface::deleteCategory($url,$categoryId) ");
        $returned=-1;

        $xml_in = "<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <DELETE_CATEGORY ID="{$categoryId}" />
                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->categories[0]['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::deleteCategory  success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::deleteCategory return $returned");
        return $returned;
    }

    /**
     *
     * Applies a rule to a group attached to a project.
     *
     * @param int $$catID the group id
     * @param int $groupID the group id
     * @param string $ruleName the rule to be applied to the group
     *
     * @return true if the rule is applied successfully or false otherwise
     */
    function applyRule($url,$catID,$roleID,$ruleID){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::applyRule($url,$catID,$roleID,$ruleID)");
        $returned=-1;
         
        $xml_in = "<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <APPLY_RULE_TO_GROUP GROUP_ID="{$roleID}" 
                                      CATEGORY_ID="{$catID}" 
                                      RULE_ID="{$ruleID}"  />
                        </REQUEST>
                    </PHPBB>

END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['GROUP']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::applyRule success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::applyRule return $returned");
        return $returned;
    }
/*    
    function getRuleFromRole($url,$catID,$roleID){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::getRuleFromRole($url,$catID,$roleID)");
        $returned=-1;
         
        $xml_in = "<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <GET_RULE GROUP_ID="{$roleID}" 
                                      CATEGORY_ID="{$catID}"  />
                        </REQUEST>
                    </PHPBB>

END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->rules[0]['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::getRuleFromRole success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::getRuleFromRole return $returned");
        return $returned;
    }
  */  
    function getRulesFromRoles($url,$catID,$arr_roleID,&$arr_ruleID){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::getRuleFromRole($url,$catID)");
        $returned=-1;
        
        
        $xml_in = "<?xml version=\"1.0\" ?>";   
        $xml_in .= "<PHPBB><REQUEST>"; 
        foreach($arr_roleID as $role_id){
            $xml_in .=<<<END
                    <GET_RULE GROUP_ID="{$role_id}" CATEGORY_ID="{$catID}"  />
END;

        }
        $xml_in .= "</REQUEST></PHPBB>";     
        
        echo $xml_in;
        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) )
            {   

                foreach($parserResponse->rules as $rule){
                    $arr_ruleID[] = $rule['ID'];
                }
                $returned = count($arr_ruleID);
                syslog (LOG_INFO, ">> PluginPhpBBInterface::getRuleFromRole success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::getRuleFromRole return $returned");
        return $returned;
    }

    /**
     * Adds a user into a group
     *
     * @param string $userName the user name
     * @param integer $group_id the user group id
     * @return boolean
     */
    function addUserToGroup($url,$group_id,$userName){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::addUserToGroup($url,$group_id,$userName)");
        $returned=-1;
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                        <ADD_USER_TO_GROUP   USER_NAME="{$userName}" 
                                             GROUP_ID="{$group_id}"  />

                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['GROUP']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::addUserToGroup success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::addUserToGroup return $returned");
        return $returned;
    }

    function removeAllUsers($url,$group_id){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::removeAllUsers($url,$group_id)");
        $returned=-1;
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <REMOVE_ALL_USERS  GROUP_ID="{$group_id}"  />
                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['GROUP']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::removeAllUsers success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::removeAllUsers return $returned");
        return $returned;
    }
    
    
    /**
     * Removes a user into a group
     *
     * @param string $userName the user name
     * @param integer $group_id the user group id
     * @return boolean
     */
    function removeUserToGroup($url,$group_id,$userName){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::removeUserToGroup($url,$group_id,$userName)");
        $returned=-1;
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                        <REMOVE_USER_TO_GROUP   USER_NAME="{$userName}" 
                                             GROUP_ID="{$group_id}"  />

                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['GROUP']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::removeUserToGroup success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::removeUserToGroup return $returned");
        return $returned;
    }

    function getSubCategories($url,$parent_id){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::getSubCategories($url) ");
        $returned = array();
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <GET_SUB_CATEGORIES PARENT_ID="{$parent_id}"/>
                        </REQUEST>
                    </PHPBB>
END;
$returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                foreach($parserResponse->categories as $key => $cat){
                    $returned[] = $cat;
                }

                syslog (LOG_INFO, ">> PluginPhpBBInterface::getSubCategories success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::getSubCategories return $returned");
        return $returned;
    }
    /**
     *
     * Supplies a set of Rules available
     *
     * @return array A set of Rules
     *
     */
    function getAvailableRules($url) {
        syslog (LOG_INFO, ">> PluginPhpBBInterface::getAvailableRules($url) ");
        $returned = array();
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <GET_AVAILABLE_RULES/>
                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                foreach($parserResponse->rules as $key => $rule){
                    $id = $rule['ID'];
                    $returned[$id] = $rule['VALUE'];
                }

                syslog (LOG_INFO, ">> PluginPhpBBInterface::getAvailableRules success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::getAvailableRules return $returned");
        return $returned;
    }

    function getBookmarks($url,$userName){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::getBookmarks($url,$userName) ");
        $returned = array();

        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <GET_BOOKMARKS USER_NAME="{$userName}"/>
                        </REQUEST>
                    </PHPBB>
END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                foreach($parserResponse->bookmarks as $key => $bookmark){
                    if( (isset($bookmark['NAME']) && !empty($bookmark['NAME'])) 
                      || (isset($bookmark['MESSAGE']) && !empty($bookmark['MESSAGE'])) ){
                        $returned[] = $bookmark;
                    }

                }

                syslog (LOG_INFO, ">> PluginPhpBBInterface::getBookmarks success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::getBookmarks return $returned");
        return $returned;
    }
    
    /**
     * Test the existence of a User in PhpBB
     *
     * @param User $usr user to check
     * @return boolean returns true if the user exists
     */
    function existsUser($url,$usrName) {
        syslog (LOG_INFO, ">> PluginPhpBBInterface::existsUser($url,$usrName) ");
        $returned=-1;
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <EXIST_USER NAME="{$usrName}"  />
                        </REQUEST>
                    </PHPBB>

END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['USER']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::existsUser success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::existsUser return $returned");
        return $returned;
    }

    /**
     * Test the existence of a Category which matches with the Project
     *
     * @param Project $proj the project to check
     * @return boolean returns true if the category exists
     */
    function existsCategoryID($url,$catID,&$cat_name){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::existsCategoryID($url,$catID)");
        $returned=-1;
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <EXIST_CATEGORY  ID="{$catID}"  />
                        </REQUEST>
                    </PHPBB>

END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->categories[0]['ID'];
                $cat_name = $parserResponse->categories[0]['NAME'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::existsCategoryID success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::existsCategoryID return $returned");
        return $returned;

    }

    
    function existsCategoryName($url,$parent_id,$cat_name){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::existsCategoryName($url,$cat_name)");
        $returned=-1;
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <EXIST_CATEGORY  NAME="{$cat_name}"  PARENT_ID="{$parent_id}" />
                        </REQUEST>
                    </PHPBB>

END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->categories[0]['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::existsCategoryName success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::existsCategoryName return $returned");
        return $returned;

    }
    
    /**
     * Test the existence of a Group in PhpBB
     *
     * @param Group $aRole Group to check
     * @return boolean returns true if the group exists
     */
    function existsGroup($url,$roleName){
        syslog (LOG_INFO, ">> PluginPhpBBInterface::existsGroup($url,$roleName) ");
        $returned=-1;
         
        $xml_in  ="<?xml version=\"1.0\" ?>";
        $xml_in .=<<<END
                    <PHPBB>
                        <REQUEST>
                            <EXIST_GROUP NAME="{$roleName}"  />
                        </REQUEST>
                    </PHPBB>

END;

        $returned_call = PluginPhpBBInterface::callPhpBB ($url, $xml_in, $xml_out);

        // response treatment
        $parserResponse = new ResponseParser();
        $res = $parserResponse->parse($xml_out);
        if ( $res == true )
        {
            if ( !(isset($parserResponse->error_code)) && ($parserResponse->attributes['RESPONSE']['STATUS'] == "success") )
            {
                $returned = $parserResponse->attributes['GROUP']['ID'];
                syslog (LOG_INFO, ">> PluginPhpBBInterface::existsGroup success response : $returned ");
            }
        }
        syslog (LOG_INFO, "<<  PluginPhpBBInterface::existsGroup return $returned");
        return $returned;
    }




    /**
     *
     */
    function callPhpBB ($url, $xml_in, &$xml_out)
    {
        syslog (LOG_INFO,">>callPhpBB ($url)");
        $returned=false;

        $ch = curl_init ();

        if ($ch === false)
        {
            syslog(LOG_ERR,"Error while calling function curl_init()");
        }
        else
        {

            $url = trim($url);
            if($url[strlen($url)-1] != DIRECTORY_SEPARATOR ){
                $url .= DIRECTORY_SEPARATOR;
            }
            curl_setopt ($ch, CURLOPT_URL, $url."gforge/command.php");

            // return the page in a string
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

            // timeout of the connection
            curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 20 );

            // timeout
            curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );

            // user agent
            curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );

            curl_setopt ($ch, CURLOPT_POSTFIELDS, "xml_in=$xml_in");
            $xml_out = curl_exec ($ch);
            if (($xml_out === false) || (curl_errno ($ch) != 0))
            {
                syslog(LOG_ERR,"Error while calling function curl_exec(), errno " . curl_errno ($ch) . ": " . curl_error ($ch));
            }
            else
            {
                $returned=true;
            }
        }

        syslog (LOG_INFO,"<<callPhpbb xml_out = $xml_out");
        return $returned;
    }

}
?>
