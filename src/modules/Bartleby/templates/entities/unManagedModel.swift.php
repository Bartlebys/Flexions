<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = GenerativeHelperForSwift::getCurrentClassNameWithPrefix($d).'.swift';
    // And its package.
    $f->package = 'xOS/models/';
}

//////////////////
// EXCLUSIONS
//////////////////

$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $d->name);

if (isset($excludeEntitiesWith)) {
    $exclusion = $excludeEntitiesWith;
}
foreach ($exclusion as $exclusionString) {
    if (strpos($exclusionName, $exclusionString) !== false) {
        return NULL; // We return null
    }
}

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

$isBaseObject = $d->isBaseObject() ;
$inheritancePrefix = ($isBaseObject ? '' : 'override');
$blockRepresentation=$d;

// EXPOSED
$exposedBlock='';
if ($modelsShouldConformToExposed){
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>$isBaseObject]);
    $exposedBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Exposed.swift.block.php');
}

// MAPPABLE
$mappableBlock='';
if ($modelsShouldConformToMappable){
    $mappableblockEndContent=NULL;
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>$isBaseObject,'mappableblockEndContent'=>$mappableblockEndContent]);
    $mappableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Mappable.swift.block.php');
}

// NSSecureCoding
$secureCodingBlock='';
if( $modelsShouldConformToNSSecureCoding ) {
    $decodingblockEndContent=NULL;
    $encodingblockEndContent=NULL;

    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$d,'isBaseObject'=>$isBaseObject,'decodingblockEndContent'=>$decodingblockEndContent,'encodingblockEndContent'=>$encodingblockEndContent]);
    $secureCodingBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/NSSecureCoding.swift.block.php');
}

// CODABLE
$codableBlock='';
if( $modelsShouldConformToCodable ) {
    $decodableblockEndContent=NULL;
    $encodableblockEndContent=NULL;

    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$d,'isBaseObject'=>$isBaseObject,'decodableblockEndContent'=>$decodableblockEndContent,'encodableblockEndContent'=>$encodableblockEndContent]);
    $codableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Codable.swift.block.php');
}

$inheritancePrefix = ($isBaseObject ? '' : 'override');
$inversedInheritancePrefix = ($isBaseObject ? 'override':'');
$superInit = ($isBaseObject ? 'super.init()'.cr() : 'super.init()'.cr());

if (!defined('_valueObjectPropertyValueString_DEFINED')){
    define("_valueObjectPropertyValueString_DEFINED",true);
    function _valueObjectPropertyValueString(PropertyRepresentation $property){
        if(isset($property->default)){
            if($property->type==FlexionsTypes::STRING){
                $defaultValue = $property->default;
                if (strpos($defaultValue,'$')!==false){
                    $defaultValue = ltrim($defaultValue,'$');
                    return " = $defaultValue"; // No quote
                }else{
                    return " = \"$property->default\"";
                }
            }else{
                return " = $property->default";
            }
        }
        return "";
    }
}

$typeDeclarationBlock="UnManagedModel";
if (isset($d->instanceOf)){
    $typeDeclarationBlock=$d->instanceOf;
}

// Include block
include  dirname(__DIR__).'/blocks/BarltebysSimpleIncludeBlock.swift.php';

//////////////////
// TEMPLATE
//////////////////

include __DIR__ . '/unManagedModel.swift.template.php';
