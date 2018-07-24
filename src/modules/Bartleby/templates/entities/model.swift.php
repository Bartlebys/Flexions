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

$isBaseObject = $d->isBaseObject();
$inheritancePrefix = ($isBaseObject ? '' : 'override ');
$blockRepresentation=$d;

// ManagedModel
$baseObjectBlock='';
if($isBaseObject && $d->name == GenerativeHelperForSwift::defaultBaseClass($d) ){
    $baseObjectBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/BartlebysBaseObject.swift.block');
}

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
    if($isBaseObject){
        $mappableblockEndContent=
            '           self._typeName <- map[Default.TYPE_NAME_KEY]'.cr();
    }else{
        $mappableblockEndContent=NULL;
    }
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>$isBaseObject,'mappableblockEndContent'=>$mappableblockEndContent]);
    $mappableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Mappable.swift.block.php');
}

// NSSecureCoding
$secureCodingBlock='';
if( $modelsShouldConformToNSSecureCoding ) {
    if($isBaseObject){
        $decodingblockEndContent=
            '            self._typeName=type(of: self).typeName()
';
        $encodingblockEndContent =
            '        coder.encode(self._typeName, forKey: Default.TYPE_NAME_KEY)
        
';
    }else{
        $decodingblockEndContent=NULL;
        $encodingblockEndContent=NULL;
    }
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$d,'isBaseObject'=>$isBaseObject,'decodingblockEndContent'=>$decodingblockEndContent,'encodingblockEndContent'=>$encodingblockEndContent]);
    $secureCodingBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/NSSecureCoding.swift.block.php');
}


// CODABLE
$codableBlock='';
if( $modelsShouldConformToCodable ) {
    if($isBaseObject){
        $decodableblockEndContent=
            '            self._typeName = try values.decode(String.self,forKey:.typeName)
';
        $encodableblockEndContent =
            '        try container.encode(self._typeName,forKey:.typeName)
        
';
    }else{
        $decodableblockEndContent=NULL;
        $encodableblockEndContent=NULL;
    }
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$d,'isBaseObject'=>$isBaseObject,'decodableblockEndContent'=>$decodableblockEndContent,'encodableblockEndContent'=>$encodableblockEndContent]);
    $codableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Codable.swift.block.php');
}



$inheritancePrefix = ($isBaseObject ? '' : 'override ');
$inversedInheritancePrefix = ($isBaseObject ? 'override ':'');
$superInit = ($isBaseObject ? 'super.init()'.cr() : 'super.init()'.cr());

if (!defined('_propertyValueString_DEFINED')){
    define("_propertyValueString_DEFINED",true);
    function _propertyValueString(PropertyRepresentation $property){


        if ($property->isSupervisable===false){

            ////////////////////////////
            // Property isn't supervisable
            ////////////////////////////
            if(isset($property->default)){
                if($property->type==FlexionsTypes::STRING){
                    $stringDefaultValue = $property->default;
                    if (strpos($stringDefaultValue,'$')!==false){
                        $stringDefaultValue = ltrim($stringDefaultValue,'$');
                        return " = $stringDefaultValue"; // No quote
                    }else{
                        return " = \"$property->default\"";
                    }
                }else{
                    return " = $property->default";
                }
            }
            return "";
        }else{

            $associatedCondition = $property->type == FlexionsTypes::DICTIONARY? '' :  "&& $property->name != oldValue";

            //////////////////////////
            // Property is supervisable
            //////////////////////////
            ///
        if(isset($property->default)){


            if($property->type==FlexionsTypes::STRING){
                $stringDefaultValue = $property->default;
                if (strpos($stringDefaultValue,'$')!==false){
                    $stringDefaultValue = ltrim($stringDefaultValue,'$');
                    $stringDefaultValue = "$stringDefaultValue"; // No quote
                }else {
                    $stringDefaultValue = "\"$property->default\""; // Quoted
                }
                return " = $stringDefaultValue {
    didSet { 
       if !self.wantsQuietChanges $associatedCondition {
            self.provisionChanges(forKey: \"$property->name\",oldValue: oldValue,newValue: $property->name) 
       } 
    }
}";
            }else{
                return " = $property->default  {
    didSet { 
       if !self.wantsQuietChanges $associatedCondition {
            self.provisionChanges(forKey: \"$property->name\",oldValue: oldValue".($property->type==FlexionsTypes::ENUM ? ".rawValue" : "" ).",newValue: $property->name".($property->type==FlexionsTypes::ENUM ? ".rawValue" : "" ).")  
       } 
    }
}";
}

        }
            return " {
    didSet { 
       if !self.wantsQuietChanges $associatedCondition {
            self.provisionChanges(forKey: \"$property->name\",oldValue: oldValue".($property->type==FlexionsTypes::ENUM ? "?.rawValue" : "" ).",newValue: $property->name".( $property->type==FlexionsTypes::ENUM ? "?.rawValue" : "" ) .") 
       } 
    }
}";
        }
    }
}

// Include block
include  dirname(__DIR__).'/blocks/BarltebysSimpleIncludeBlock.swift.php';

//////////////////
// TEMPLATE
//////////////////

include __DIR__.'/model.swift.template.php';
