<?php

require_once ('RequestParser.class.php');
require_once ('PhpBBHandler.php');

if (isset ($_REQUEST['xml_in']))
{
    $xml=$_REQUEST['xml_in'];
    $parserRequest = new RequestParser;
    $parserRequest->parse($xml);
}

if (isset ($_GET['a']))
{
            PhpBBHandler::getRuleFromRole(7);
}


if ( !(isset($parserRequest->error_code)))
{
    $attributes = $parserRequest->attributes;
    $xml_out = "<?xml version=\"1.0\" ?>"; 
    $xml_out .= "<PHPBB>";  
    echo $xml_out; $xml_out='';
    
    for($i = 0 ; $i< count($attributes)  ; $i++){
        $RequestName = $attributes[$i][0];
        $RequestAttribut = $attributes[$i][1];

        switch ($RequestName) {

            case "CREATE_USER":

                $response = PhpBBHandler::createUser( $RequestAttribut['NAME'],
                $RequestAttribut['EMAIL'],
                $RequestAttribut['PASSWORD'],
                $RequestAttribut['LANGUAGE'],
                $RequestAttribut['TIMEZONE']);
                if($response >0){
                    $status = "success";
                }else{
                    $status = "failure";
                }

                
                $xml_out .=<<<END
                          <RESPONSE STATUS="{$status}">
                              <USER ID="{$response}" />
                          </RESPONSE>
END;
break;
            case "CREATE_GROUP":
                $response = PhpBBHandler::createGroup($RequestAttribut['NAME']);
                 
                if($response >0){
                    $status = "success";
                }else{
                    $status = "failure";
                }

                
                $xml_out .=<<<END
                          <RESPONSE STATUS="{$status}">
                              <GROUP ID="{$response}" />
                          </RESPONSE>

END;
break;
            case "CREATE_CATEGORY":

                $response = PhpBBHandler::createCategory($RequestAttribut['PARENT_ID'],
                $RequestAttribut['NAME']);

                if($response >0){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                          <RESPONSE STATUS="{$status}">
                              <CATEGORY ID="{$response}" />
                          </RESPONSE>

END;

                 
                break;
            case "RENAME_CATEGORY":

                $response = PhpBBHandler::renameCategory($RequestAttribut['ID'],
                $RequestAttribut['NAME']);

                if($response > 0){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <CATEGORY ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
            case "DELETE_CATEGORY":

                $response = PhpBBHandler::deleteCategory($RequestAttribut['ID']);

                if($response > 0){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <CATEGORY ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
            case "APPLY_RULE_TO_GROUP":

                $response = PhpBBHandler::applyRule($RequestAttribut['CATEGORY_ID'],
                $RequestAttribut['GROUP_ID'],
                $RequestAttribut['RULE_ID']);

                if($response >0){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <GROUP ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
            case "ADD_USER_TO_GROUP":
                $response = PhpBBHandler::addUserToGroup($RequestAttribut['GROUP_ID'],
                array($RequestAttribut['USER_NAME']));

                if($response){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <GROUP ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
            case "REMOVE_USER_TO_GROUP":
                $response = PhpBBHandler::removeUserToGroup($RequestAttribut['GROUP_ID'],
                array($RequestAttribut['USER_NAME']));

                if($response){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <GROUP ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
            case "REMOVE_ALL_USERS":
                $response = PhpBBHandler::removesAllUsers($RequestAttribut['GROUP_ID']);

                if($response){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <GROUP ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
            case "GET_AVAILABLE_RULES":
                $rulesArray =  PhpBBHandler::getAvailableRules();


                if(count($rulesArray) >0){
                    $status = "success";
                }else{
                    $status = "failure";
                }

                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
END;

                foreach($rulesArray as $id => $rule_name){
                    
                    
                    $xml_out .= "<RULE ID=\"".$id."\"  VALUE=\"".$rule_name."\"></RULE>";
                }

                $xml_out .=<<<END
                          </RESPONSE>
                      
END;
break;
            case "GET_RULE":
                $response =  PhpBBHandler::getRuleFromRole($RequestAttribut['GROUP_ID'],$RequestAttribut['CATEGORY_ID']);

                if($response){
                    $status = "success";
                }else{
                    $status = "failure";
                }

                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <RULE ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
            case "GET_SUB_CATEGORIES":
                $sub_cat_array = PhpBBHandler::getSubForums($RequestAttribut['PARENT_ID']);

                if(count($sub_cat_array) >0){
                    $status = "success";
                }else{
                    $status = "failure";
                }

                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
END;

                foreach($sub_cat_array as $cat){
                    $xml_out .= "<CATEGORY ID=\"".$cat['forum_id']."\" " ;
                    $xml_out .= "NAME=\"".$cat['forum_name']."\" " ;
                    $xml_out .= "></CATEGORY>";
                }

                $xml_out .=<<<END
                          </RESPONSE>
                      
END;
break;
            case "GET_BOOKMARKS":
                $response = PhpBBHandler::getBookmarks($RequestAttribut['USER_NAME']);

                if(!$response){
                    
                    $xml_out .=<<<END
                      
                          <RESPONSE STATUS="failure">
                          </RESPONSE>
                      
END;
                }


                break;
            case "EXIST_USER":
                $response = PhpBBHandler::existsUser($RequestAttribut['NAME']);

                if($response > 0){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <USER ID="{$response}" />
                          </RESPONSE>
                      
END;
break;
            case "EXIST_CATEGORY":
                
                $ID = $RequestAttribut['ID'];
                $NAME = $RequestAttribut['NAME'];
                $PARENT_ID = $RequestAttribut['PARENT_ID'];
                
                if(isset($ID) && !empty($ID)){
                    $response = PhpBBHandler::existsCategoryID($ID,$NAME);
                }else{
                    $response = PhpBBHandler::existsCategoryName($NAME,$PARENT_ID,$ID);
                }
                
                

                if($response > 0){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <CATEGORY ID="{$response}" NAME="{$NAME}" />
                          </RESPONSE>
                      

END;
break;
            case "EXIST_GROUP":
                $response = PhpBBHandler::existsGroup($RequestAttribut['NAME']);

                if($response > 0){
                    $status = "success";
                }else{
                    $status = "failure";
                }
                
                $xml_out .=<<<END
                      
                          <RESPONSE STATUS="{$status}">
                              <GROUP ID="{$response}" />
                          </RESPONSE>
                      

END;
break;
        }
        
    }
        echo ($xml_out);
        $xml_out = "</PHPBB>"; 
        echo ($xml_out);
}
else
{
    /*
     *treatment :  xml parser error
     */
    syslog(LOG_ERR,"error to parse the request ");
    syslog(LOG_ERR,"code  = ".$parserRequest->error_code);
    syslog(LOG_ERR,"error   = ".$parserRequest->error_string);
    syslog(LOG_ERR,"current_line  = ".$parserRequest->current_line);
    syslog(LOG_ERR,"current_column  = ".$parserRequest->current_column);
}

?>
