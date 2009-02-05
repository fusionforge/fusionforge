<?php 
require_once(dirname(__FILE__) . '/ServiceHeaders.php');
require_once('plugins/report/include/facade/JavancssReportFacade.php');

$server = new soap_server;
$server->configureWSDL("JavaNCSSService", $gNamespace, $gNamespace . '/JavaNCSSService.php');


// Fonctions accéssibles depuis le service Web.
$server->register('addJavancssReport',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'javancssDTOCollection'         => 'tns:JavancssDTOCollection',
                        'javancssPackageDTOCollection'  => 'tns:JavancssPackageDTOCollection',
                        'javancssObjectDTOCollection'   => 'tns:JavancssObjectDTOCollection',
                        'javancssFunctionDTOCollection' => 'tns:JavancssFunctionDTOCollection',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/JavaNCSSService.php',                   // namespace
                  $gNamespace . '/JavaNCSSService.php/addJavancssReport', // SOAPAction
                  'rpc',                                                // style
                  'encoded'                                             // use                  
);

$server->register('deleteJavancssReportByMavensIds',
                  array('userName'      => 'xsd:string', 
                        'userPw'        => 'xsd:string',
                        'unixGroupName' => 'xsd:string',
                        'mavenArtefactId' => 'xsd:string',
                        'mavenGroupId' => 'xsd:string',
                        'mavenVersion' => 'xsd:string'),
                  array('return' => 'xsd:boolean'),
                  $gNamespace . '/JavaNCSSService.php',                   // namespace
                  $gNamespace . '/JavaNCSSService.php/deleteJavancssReportByMavensIds', // SOAPAction
                  'rpc',                                                // style
                  'encoded'                                             // use                  
);


// Définition des types complexes.
$server->wsdl->addComplexType(
    'JavancssDTO',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'reportDate' => array('name' => 'reportDate', 'type' => 'xsd:string'),
        'reportTime' => array('name' => 'reportTime', 'type' => 'xsd:string')
    )
);

$server->wsdl->addComplexType(
    'JavancssFunctionDTO',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'name'     => array('name' => 'name',     'type' => 'xsd:string'),
        'ncss'     => array('name' => 'ncss',     'type' => 'xsd:int'   ),
        'ccn'      => array('name' => 'ccn',      'type' => 'xsd:int'   ),
        'javadocs' => array('name' => 'javadocs', 'type' => 'xsd:int'   )
    )
);

$server->wsdl->addComplexType(
    'JavancssObjectDTO',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'name'     => array('name' => 'name',     'type' => 'xsd:string'),
        'ncss'     => array('name' => 'ncss',     'type' => 'xsd:int'   ),
        'functions'=> array('name' => 'functions','type' => 'xsd:int'   ),
        'classes'  => array('name' => 'classes',  'type' => 'xsd:int'   ),       
        'javadocs' => array('name' => 'javadocs', 'type' => 'xsd:int'   )
    )
);

$server->wsdl->addComplexType(
    'JavancssPackageDTO',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'name'               => array('name' => 'name',               'type' => 'xsd:string'),
        'classes'            => array('name' => 'classes',            'type' => 'xsd:int'   ),
        'functions'          => array('name' => 'functions',          'type' => 'xsd:int'   ),
        'ncss'               => array('name' => 'ncss',               'type' => 'xsd:int'   ),
        'javadocs'           => array('name' => 'javadocs',           'type' => 'xsd:int'   ),        
        'javadocLines'       => array('name' => 'javadocLines',       'type' => 'xsd:int'   ),
        'singleCommentLines' => array('name' => 'singleCommentLines', 'type' => 'xsd:int'   ),
        'multiCommentLines'  => array('name' => 'multiCommentLines',  'type' => 'xsd:int'   )
    )
);

$server->wsdl->addComplexType('JavancssDTOCollection',
                'complexType',
                'array',
                '',
                'SOAP-ENC:Array',
                array(),
                array(
                    array('ref'=>'SOAP-ENC:arrayType',
                    'wsdl:arrayType'=>'tns:JavancssDTO[]')
                ),
                'tns:JavancssDTO'
);

$server->wsdl->addComplexType('JavancssPackageDTOCollection',
                'complexType',
                'array',
                '',
                'SOAP-ENC:Array',
                array(),
                array(
                    array('ref'=>'SOAP-ENC:arrayType',
                    'wsdl:arrayType'=>'tns:JavancssPackageDTO[]')
                ),
                'tns:JavancssPackageDTO'
);

$server->wsdl->addComplexType('JavancssObjectDTOCollection',
                'complexType',
                'array',
                '',
                'SOAP-ENC:Array',
                array(),
                array(
                    array('ref'=>'SOAP-ENC:arrayType',
                    'wsdl:arrayType'=>'tns:JavancssObjectDTO[]')
                ),
                'tns:JavancssObjectDTO'
);

$server->wsdl->addComplexType('JavancssFunctionDTOCollection',
                'complexType',
                'array',
                '',
                'SOAP-ENC:Array',
                array(),
                array(
                    array('ref'=>'SOAP-ENC:arrayType',
                    'wsdl:arrayType'=>'tns:JavancssFunctionDTO[]')
                ),
                'tns:JavancssFunctionDTO'
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);

/**
 * Ajoute un rapport Javancss en vérifiant que l'utilisateur a le droit de le faire.
 * 
 * @param userName le login de l'utilisateur.
 * @param userPw le password de l'utilisateur.
 * @param unixGroupName le nom unix du projet.
 * @param javancssDTOCollection le tableau des informations générales sur le rapport Javancss.
 * @param javancssPackageDTOCollection le tableau des informations sur les packages.
 * @param javancssObjectDTOCollection le tableau des informations sur les objects.
 * @param javancssFunctionDTOCollection le tableau des informations sur les fonctions.
 * @return vrai si l'ajout a réussi.
 */
function addJavancssReport($userName, $userPw, $unixGroupName, $javancssDTOCollection, $javancssPackageDTOCollection, $javancssObjectDTOCollection, $javancssFunctionDTOCollection, $mavenArtefactId, $mavenGroupId, $mavenVersion){

    $groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
   
    $javancssDTO = $javancssDTOCollection[0]; 
    
    $dto = new JavancssDTO();
    $dto->setReportDate($javancssDTO["reportDate"]);
    $dto->setReportTime($javancssDTO["reportTime"]);
    $dto->setGroupId($groupId);
    
    $javancssId = JavancssReportFacade::addJavancssReport($dto, $mavenArtefactId, $mavenGroupId, $mavenVersion);
    
    if($javancssId == false){
        TransactionFacade::rollback();
        return false;
    }
    
    foreach($javancssPackageDTOCollection as $javancssPackageDTO){
    
        $dto = new JavancssPackageDTO();
        $dto->setName($javancssPackageDTO["name"]);
        $dto->setClasses($javancssPackageDTO["classes"]);
        $dto->setFunctions($javancssPackageDTO["functions"]);
        $dto->setNcss($javancssPackageDTO["ncss"]);
        $dto->setJavadocs($javancssPackageDTO["javadocs"]);
        $dto->setJavadocLines($javancssPackageDTO["javadocLines"]);
        $dto->setSingleCommentLines($javancssPackageDTO["singleCommentLines"]);
        $dto->setMultiCommentLines($javancssPackageDTO["multiCommentLines"]);
        $dto->setJavancssId($javancssId);
    
        if(JavancssReportFacade::addJavancssPackageReport($dto) === false){
            TransactionFacade::rollback();
            return false;
        }
    }
    
    foreach($javancssObjectDTOCollection as $javancssObjectDTO){
    
        $dto = new JavancssObjectDTO();
        $dto->setName($javancssObjectDTO["name"]);
        $dto->setClasses($javancssObjectDTO["classes"]);
        $dto->setFunctions($javancssObjectDTO["functions"]);
        $dto->setNcss($javancssObjectDTO["ncss"]);
        $dto->setJavadocs($javancssObjectDTO["javadocs"]);
        $dto->setJavancssId($javancssId);
    
        if(JavancssReportFacade::addJavancssObjectReport($dto) === false){
            TransactionFacade::rollback();
            return false;
        }
    }
    
    foreach($javancssFunctionDTOCollection as $javancssFunctionDTO){
    
        $dto = new JavancssFunctionDTO();
        $dto->setName($javancssFunctionDTO["name"]);
        $dto->setNcss($javancssFunctionDTO["ncss"]);
        $dto->setCcn($javancssFunctionDTO["ccn"]);
        $dto->setJavadocs($javancssFunctionDTO["javadocs"]);
        $dto->setJavancssId($javancssId);
        
        if(JavancssReportFacade::addJavancssFunctionReport($dto) === false){
            TransactionFacade::rollback();
            return false;
        }
    }    
    
    TransactionFacade::commit();
    
    return true;
}

function deleteJavancssReportByMavensIds($userName, $userPw, $unixGroupName, $mavenArtefactId, $mavenGroupId, $mavenVersion){
	
	  $groupId = GroupFacade::getGroupId($userName, $userPw, $unixGroupName);
    
    if($groupId === false){
        return false;
    }
    
    TransactionFacade::begin();
    
    if(JavancssReportFacade::deleteJavancssReportByMavensIds($groupId, $mavenArtefactId, $mavenGroupId, $mavenVersion) === false){
        TransactionFacade::rollback();
        return false;
    }
    
    TransactionFacade::commit();
    
    return true;
}
?>