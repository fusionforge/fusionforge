<?php 
require_once(dirname(__FILE__) . '/ServiceHeaders.php');
require_once('plugins/report/include/facade/MavenInfoFacade.php');

$server = new soap_server;
$server->configureWSDL("MavenInfoService", $gNamespace, $gNamespace . '/MavenInfoService.php');


$server->register('addMavenInfo',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/MavenInfoService.php',                     // namespace
                  $gNamespace . '/MavenInfoService.php/addMavenInfo', // SOAPAction
                  'rpc',                                                  // style
                  'encoded'                                               // use   
);

$server->register('deleteMavenInfoByMavensIds',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/MavenInfoService.php',                   // namespace
                  $gNamespace . '/MavenInfoService.php/deleteMavenInfoByMavensIds', // SOAPAction
                  'rpc',                                                // style
                  'encoded'                                             // use                  
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);


/**
 * Ajoute les infos maven en vérifiant que l'utilisateur a le droit de le faire.
 * 
 * @param userName le login de l'utilisateur.
 * @param userPw le password de l'utilisateur.
 * @param unixGroupName le nom unix du projet.
 * @return vrai si l'ajout a réussi.
 */
function addMavenInfo($userName, $userPw, $unixGroupName, $mavenArtefactId, $mavenGroupId, $mavenVersion){
	
    $groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
    
                             
    if(MavenInfoFacade::addMavenInfo($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion) === false){
    	TransactionFacade::rollback();
        return false;
    }
    
    TransactionFacade::commit();
    
    return true;
}

function deleteMavenInfoByMavensIds($userName, $userPw, $unixGroupName, $mavenArtefactId, $mavenGroupId, $mavenVersion){
	
	$groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
    
    if(MavenInfoFacade::deleteMavenInfoByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion) === false){
        TransactionFacade::rollback();
        return false;
    }
    
    TransactionFacade::commit();
    
    return true;
}

?>