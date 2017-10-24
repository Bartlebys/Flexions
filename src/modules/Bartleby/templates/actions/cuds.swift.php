<?php

include FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';

/*
 * SWIFT 3.X template
 * This weak logic template is compliant with Bartleby 1.0 approach.
 * It allows to update easily very complex templates.gt
 * It is not logic less but the logic intent to be as weak as possible
 */
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';



if (isset($f, $d, $h)) {

    /* @var $f Flexed */
    /* @var $d ActionRepresentation */
    /* @var $h Hypotypose */

    // We use explicit name (!)
    // And reserve $f , $d , $h possibly for blocks

    $flexed = $f;
    $actionRepresentation = $d;
    $hypotypose = $h;

    $flexed->fileName = $actionRepresentation->class . '.swift';
    $flexed->package = 'xOS/operations/';
    if(isset($shouldImplementExecuteBlock) && $shouldImplementExecuteBlock==false){
        $flexed->package = 'xOS/operations/bases/';
    }

} else {
    return NULL;
}

/* @var $flexed Flexed */
/* @var $actionRepresentation ActionRepresentation */
/* @var $hypotypose Hypotypose */


/////////////////
// EXCLUSIONS
/////////////////



// Should this Action be excluded ?

$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}

// This template cannot be used for GET Methods
// We use  endpoint.swift.template.php
if ($actionRepresentation->httpMethod === 'GET') {
    return NULL;
}

// We want also to exclude by query
// We use  endpoint.swift.template.php
if (!(strpos($d->class, 'ByQuery') === false)) {
    return NULL;
}

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

// Compute ALL the Variables you need in the template
// Including generative blocks.



// Include block
$includeBlock = '';
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons == true) {
    $includeBlock .= stringIndent("import Alamofire", 1);
} else {
    $includeBlock .= stringIndent("import Alamofire", 1);
    $includeBlock .= stringIndent("import BartlebyKit", 1);
}

$j=json_encode($actionRepresentation,true);

$httpMethod = $actionRepresentation->httpMethod;
$pluralizedName = lcfirst($actionRepresentation->collectionName);
$singularName = lcfirst(Pluralization::singularize($pluralizedName));
$baseClassName = ucfirst($actionRepresentation->class);
$ucfSingularName = ucfirst($singularName);
$ucfPluralizedName = ucfirst($pluralizedName);

$actionString = NULL;
$localAction = NULL;

$registrySyntagm = 'in';
if ($httpMethod == "POST") {
    $actionString = 'creation';
    $localAction = 'upsert';
} elseif ($httpMethod == "PUT") {
    $actionString = 'update';
    $localAction = 'upsert';
} elseif ($httpMethod == "PATCH") {
    $actionString = 'update';
    $localAction = 'upsert';
} elseif ($httpMethod == "DELETE") {
    $actionString = 'deleteByIds';
    $localAction = 'deleteByIds';
    $registrySyntagm = 'from';
} else {
    $actionString = 'NO_FOUND';
    $localAction = 'NO_FOUND';
}

$firstParameterName = NULL;
$firstParameterTypeString = NULL;
$varName = NULL;
$executeArgumentSerializationBlock = NULL;
/* @var $firstParameter PropertyRepresentation */
$firstParameter = NULL;
$handlesCollection=false;

while ($actionRepresentation->iterateOnParameters()) {
    /*@var $parameter PropertyRepresentation*/
    $parameter = $actionRepresentation->getParameter();
    // We use the first parameter.

    if (!isset($varName, $firstParameterName, $firstParameterTypeString)) {
        $firstParameter = $parameter;
        $firstParameterName = $parameter->name;
        $handlesCollection=($firstParameter->type == FlexionsTypes::COLLECTION);
    }
}

// Note on deletion.
// The swift implementation uses full object (let's User)
// When the endPoints use ids.
// So we need to remap variable to reflect this non symetric situation.
if ($httpMethod != 'DELETE'){
    $privateMemberName = '_' . $firstParameterName;
    $subjectName = $firstParameterName;
    $subjectStringType = $handlesCollection ? "[$firstParameter->instanceOf]" : $firstParameter->instanceOf;
    $subjectUnitaryType = $firstParameter->instanceOf;
}else{
    $privateMemberName = '_' .($handlesCollection ? $pluralizedName : $singularName);
    $subjectName = $handlesCollection ? $pluralizedName : $singularName;
    $subjectStringType = $handlesCollection ? "[$ucfSingularName]" : $ucfSingularName;
    $subjectUnitaryType = $ucfSingularName;
}

if ($handlesCollection) {
    if ($httpMethod != 'DELETE') {
        $firstParameterTypeString = '[' . $ucfSingularName . ']';
        $executeArgumentSerializationBlock = "
                var parameters = [String: Any]()
                var collection = [[String: Any]]()
                for $singularName in $pluralizedName{
                    let serializedInstance = $singularName.dictionaryRepresentation()
                    collection.append(serializedInstance)
                }
                parameters[\"$pluralizedName\"]=collection" . cr();
    } else {
        $firstParameterTypeString = '[String]';
        $executeArgumentSerializationBlock = "
                var parameters = [String: Any]()
                parameters[\"ids\"] = $subjectName.map{\$0.UID}" . cr();
    }
    $varName = $pluralizedName;
} else {
    if ($httpMethod != 'DELETE') {
        $firstParameterTypeString = $ucfSingularName;
        $executeArgumentSerializationBlock = "
                var parameters = [String: Any]()
                parameters[\"$singularName\"] = $firstParameterName.dictionaryRepresentation()" . cr();
    } else {
        $firstParameterTypeString = 'String';
        $executeArgumentSerializationBlock = "
                var parameters = [String: Any]()
                parameters[\"" . $singularName . "Id\"] = " . $subjectName . ".UID" . cr();
    }
    $varName = $singularName;
}

if(!isset($shouldImplementExecuteBlock)){
    $shouldImplementExecuteBlock=true;
}

//////////////////////////////
//
// THIS IS A COMPLEX CASE
// READ CAREFULLY
//
// We want to serialize the parameters as Codable And/Or Mappable And/Or NSSecureCoding
// and  not to serialize globally the operation
// as the operation will serialize this instance in its data dictionary.
//
// We Gonna inject the relevant private properties.
// #1 Create a virtual entity
// #2 Inject the PropertyRepresentation
////////////////////////////////

/* @var $virtualEntity EntityRepresentation */

$virtualEntity = new EntityRepresentation();
$virtualEntity->name = "payload";
$_ENTITY_rep = new PropertyRepresentation();
$_ENTITY_rep->name = "_payload";
$_ENTITY_rep->type = FlexionsTypes::DATA;
$_ENTITY_rep->required = true;
$_ENTITY_rep->isDynamic = false;
$_ENTITY_rep->isGeneratedType = true;
$virtualEntity->properties[] = $_ENTITY_rep;


// Acknowledgement Block
$acknowledgementBlock = '
if index.intValue >= 0 {
    // -2 means the trigger relay has been discarded (the status code can be in 200...299
    // -1 means an error has occured (the status code should be >299
    let acknowledgment=Acknowledgment()
    acknowledgment.httpContext=context
    acknowledgment.operationName="'.$baseClassName.'"
    acknowledgment.triggerIndex=index.intValue
    acknowledgment.latency=timeline.latency
    acknowledgment.requestDuration=timeline.requestDuration
    acknowledgment.serializationDuration=timeline.serializationDuration
    acknowledgment.totalDuration=timeline.totalDuration
    acknowledgment.triggerRelayDuration=triggerRelayDuration.doubleValue';

if ($parameter->type == FlexionsTypes::COLLECTION) {
    $acknowledgementBlock .= cr()."    acknowledgment.uids=$subjectName.map({\$0.UID})";
} else {
    $acknowledgementBlock .= cr()."    acknowledgment.uids=[$subjectName.UID]";
}
$acknowledgementBlock .= cr().'    document.record(acknowledgment)';
$acknowledgementBlock .= cr().'    document.report(acknowledgment) // Acknowlegments are also metrics
}';
$acknowledgementBlock = stringIndent($acknowledgementBlock, 10);
$acknowledgementBlock = cr() . $acknowledgementBlock;


// Operation identification block

$operationIdentificationBlock = cr();
if ($parameter->type == FlexionsTypes::COLLECTION) {
    $operationIdentificationBlock .= stringIndent('let stringIDS=PString.ltrim(' . $subjectName . '.reduce("", { $0+","+$1.UID }),characters:",")', 4);
    $operationIdentificationBlock .= stringIndent('pushOperation.summary="\(operationInstance.runTimeTypeName())(\(stringIDS))"', 4);
} else {
    $operationIdentificationBlock .= stringIndent('pushOperation.summary="\(operationInstance.runTimeTypeName())(\('. $subjectName . '.UID))"', 4);
}

// Operation commit block
$operationCommitBlock = '';
if ($httpMethod != "DELETE") {
    $operationCommitBlock .= cr();
    if ($parameter->type == FlexionsTypes::COLLECTION) {
        $operationCommitBlock .= stringIndent("for item in $subjectName{",4);
        $operationCommitBlock .= stringIndent("Bartleby.markCommitted(item.UID)", 5);
        $operationCommitBlock .= stringIndent("}", 4);
    } else {
        $operationCommitBlock .= stringIndent("Bartleby.markCommitted($firstParameterName.UID)", 4);
    }
}


// Distributed Block
$provisionnedBlock = '';
if ($httpMethod != "DELETE") {
    $provisionnedBlock .= '';
    if ($parameter->type == FlexionsTypes::COLLECTION) {
        $provisionnedBlock .= stringIndent("for item in $subjectName{", 5);
        $provisionnedBlock .= stringIndent("Bartleby.markProvisionned(item.UID)", 6);
        $provisionnedBlock .= stringIndent("}", 5);
    } else {
        $provisionnedBlock .= stringIndent("Bartleby.markProvisionned($subjectName.UID)", 5);
    }
}

$payloadDeserialization = '';
if ($parameter->type == FlexionsTypes::COLLECTION) {
    $payloadDeserialization .= "try JSON.decoder.decode([$subjectUnitaryType].self, from:self._payload ?? Data())";
} else {
    $payloadDeserialization .= "try JSON.decoder.decode($subjectUnitaryType.self, from:self._payload ?? Data())";
}

// This is a complex Case.
// We Use the $virtualEntity for the blocks
// Check it's definition for more information


// Exposed Block
$exposedBlock = '';
if ($modelsShouldConformToExposed) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation' => $virtualEntity, 'isBaseObject' => false]);
    $exposedBlock = stringFromFile(FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/Exposed.swift.block.php');
}
$mappableBlock = '';
if ($modelsShouldConformToMappable) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation' => $virtualEntity, 'isBaseObject' => false, 'mappableblockEndContent' => '']);
    $mappableBlock = stringFromFile(FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/Mappable.swift.block.php');
}
$secureCodingBlock = '';
if ($modelsShouldConformToNSSecureCoding) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation' => $virtualEntity, 'isBaseObject' => false, 'decodingblockEndContent' => '', 'encodingblockEndContent' => '']);
    $secureCodingBlock = stringFromFile(FLEXIONS_MODULES_DIR . '/Bartleby/templates/blocks/NSSecureCoding.swift.block.php');
}


// CODABLE
$codableBlock='';
if( $modelsShouldConformToCodable ) {
    $decodableblockEndContent=NULL;
    $encodableblockEndContent=NULL;

    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$virtualEntity,'isBaseObject'=>false,'decodableblockEndContent'=>$decodableblockEndContent,'encodableblockEndContent'=>$encodableblockEndContent]);
    $codableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Codable.swift.block.php');
}

/////////////////////////
// WEAK LOGIC TEMPLATE
/////////////////////////

include __DIR__ . '/cuds.withWeakLogic.swift.template.php';