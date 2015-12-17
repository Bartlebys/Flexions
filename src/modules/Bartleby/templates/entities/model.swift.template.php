<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = GenerativeHelperForSwift::getCurrentClassNameWithPrefix($d).'.swift';
    // And its package.
    $f->package = 'xOS/models/';
}

// Exclusion

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


if (!defined('_propertyValueString_DEFINED')){
    define("_propertyValueString_DEFINED",true);
    function _propertyValueString(PropertyRepresentation $property){
        if(isset($property->default)){
            if($property->type==FlexionsTypes::STRING){
                return " = \"$property->default\"";
            }else{
                return " = $property->default";
            }

        }
        return "?";
    }
}



/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
import ObjectMapper

// MARK: Model <?php echo ucfirst($d->name)?>

@objc(<?php echo ucfirst($d->name)?>) class <?php echo ucfirst($d->name)?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$d); ?>{


<?php
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    if($property->description!=''){
        echoIndentCR('//' .$property->description. cr(), 1);
    }
    if($property->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        echoIndentCR('enum ' .$enumTypeName.':'.ucfirst($property->instanceOf). '{', 1);
        foreach ($property->enumerations as $element) {
            if($property->instanceOf==FlexionsTypes::STRING){
                echoIndentCR('case ' .ucfirst($element).' = "'.$element.'"', 2);
            }else{
                echoIndentCR('case ' .ucfirst($element).' = '.$element.'', 2);
            }
        }
        echoIndentCR('}', 1);
        echoIndentCR('var ' . $name .':'.$enumTypeName._propertyValueString($property), 1);
    }else if($property->type==FlexionsTypes::COLLECTION){
        echoIndentCR('var ' . $name .':['.ucfirst($property->instanceOf). ']?', 1);
    }else if($property->type==FlexionsTypes::OBJECT){
        echoIndentCR('var ' . $name .':'.ucfirst($property->instanceOf)._propertyValueString($property), 1);
    }else{
        $nativeType=FlexionsSwiftLang::nativeTypeFor($property->type);
        if(strpos($nativeType,FlexionsTypes::NOT_SUPPORTED)===false){
            echoIndentCR('var ' . $name .':'.$nativeType._propertyValueString($property), 1);
        }else{
            echoIndentCR('var ' . $name .':Not_Supported = Not_Supported()//'. ucfirst($property->type), 1);
        }
    }
    echoIndentCR('',0);
}?>

<?php

if( $modelsShouldConformToNSCoding ) {

    echo('
    // MARK: NSCoding

    required init(coder decoder: NSCoder) {
        super.init(coder: decoder)'.cr());
    GenerativeHelperForSwift::echoBodyOfInitWithCoder($d, 2);
    echo( '
    }

    override func encodeWithCoder(aCoder: NSCoder) {
        super.encodeWithCoder(aCoder)'.cr());
    GenerativeHelperForSwift::echoBodyOfEncodeWithCoder($d, 2);
    echo('
    }
   ');
}

?>
    required init(){
        super.init()
    }

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init(map)
        mapping(map)
    }



    override func mapping(map: Map) {
        super.mapping(map)
<?php
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    echoIndentCR($name . ' <- map["' . $name . '"]', 2);
}
?>
    }

    // MARK : Identifiable

    override class var collectionName:String{
        return "<?php echo lcfirst(Pluralization::pluralize($d->name)) ?>"
    }

    override var d_collectionName:String{
        return <?php echo ucfirst($d->name)?>.collectionName
    }


    // MARK : Persistent

    override func toPersistentRepresentation()->(UID:String,collectionName:String,serializedUTF8String:String,A:Double,B:Double,C:Double,D:Double,E:Double,S:String){
        var r=super.toPersistentRepresentation()
        r.A=NSDate().timeIntervalSince1970
        return r
    }

}

<?php /*<- END OF TEMPLATE */?>