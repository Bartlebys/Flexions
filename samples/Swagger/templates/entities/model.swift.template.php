<?php


include FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';

require_once FLEXIONS_ROOT_DIR.'/flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/GenerativeHelperForSwift.class.php';
require_once FLEXIONS_MODULES_DIR.'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = GenerativeHelperForSwift::getCurrentClassNameWithPrefix($d).'.swift';
    // And its package.
    $f->package = 'iOS/swift/models/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation


// MARK: Model <?php echo ucfirst($d->name)?>

class <?php echo ucfirst($d->name)?> : NSObject,NSCoding,Mappable{
<?php
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    if($d->firstProperty()){
        echoIndent(cr(),0);
    }else{
        echoIndent(cr(),0);
    }
    if($property->description!=''){
        echoIndent('//' .$property->description. cr(), 1);
    }
    if($property->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        echoIndent('enum ' .$enumTypeName.':'.ucfirst($property->instanceOf). '{' . cr(), 1);
        foreach ($property->enumerations as $element) {
            if($property->instanceOf==FlexionsTypes::STRING){
                echoIndent('case ' .ucfirst($element).' = "'.$element.'"' . cr(), 2);
            }else{
                echoIndent('case ' .ucfirst($element).' = '.$element.'' . cr(), 2);
            }
        }
        echoIndent('}' . cr(), 1);
        echoIndent('var ' . $name .':'.$enumTypeName.'?' . cr(), 1);
    }else if($property->type==FlexionsTypes::COLLECTION){
        echoIndent('var ' . $name .':['.ucfirst($property->instanceOf). ']?' . cr(), 1);
    }else if($property->type==FlexionsTypes::OBJECT){
        echoIndent('var ' . $name .':'.ucfirst($property->instanceOf). '?' . cr(), 1);
    }else{
        $nativeType=FlexionsSwiftLang::nativeTypeFor($property->type);
        if(strpos($nativeType,FlexionsTypes::NOT_SUPPORTED)===false){
            echoIndent('var ' . $name .':'.$nativeType. '?' . cr(), 1);
        }else{
            echoIndent('var ' . $name .':Not_Supported = Not_Supported()//'. ucfirst($property->type). cr(), 1);
        }
    }
    if($d->lastProperty()){
        echoIndent(cr(),0);
    }
}?>
    override init(){}

    // MARK: NSCoding

    required init(coder decoder: NSCoder) {
<?php
// NSCoding support
while ($d->iterateOnProperties() === true) {
    $property = $d->getProperty();
    $name = $property->name;
    $flexionsType = $property->type;
    $nativeType = FlexionsSwiftLang::nativeTypeFor($flexionsType);
    switch ($flexionsType) {
        case FlexionsTypes::STRING:
            echoIndent($name . '=decoder.decodeObjectForKey("' . $name . '") as? ' . $nativeType . '' . cr(), 2);
            break;
        case FlexionsTypes::INTEGER:
            echoIndent($name . '=decoder.decodeIntegerForKey("' . $name . '")' . cr(), 2);
            break;
        case FlexionsTypes::BOOLEAN:
            echoIndent($name . '=decoder.decodeBoolForKey("' . $name . '")' . cr(), 2);
            break;
        case FlexionsTypes::OBJECT:
            echoIndent($name . '=decoder.decodeObjectForKey("' . $name . '") as? ' . ucfirst($property->instanceOf) . '' . cr(), 2);
            break;
        case FlexionsTypes::COLLECTION:
            echoIndent($name . '=decoder.decodeObjectForKey("' . $name . '") as? [' . ucfirst($property->instanceOf) . ']' . cr(), 2);
            break;
        case FlexionsTypes::ENUM:
            $enumTypeName=ucfirst($name);
            echoIndent($name . '=decoder.decodeObjectForKey("' . $name . '") as? '.$enumTypeName . cr(), 2);
            break;
        case FlexionsTypes::FILE:
            echoIndent($name . '=decoder.decodeObjectForKey("' . $name . '") as? ' . ucfirst($property->instanceOf) . '' . cr(), 2);
            break;
        case FlexionsTypes::FLOAT:
            echoIndent($name . '=decoder.decodeFloatForKey("' . $name . '")' . cr(), 2);
            break;
        case FlexionsTypes::DOUBLE:
            echoIndent($name . '=decoder.decodeDoubleForKey("' . $name . '")' . cr(), 2);
            break;
        case FlexionsTypes::BYTE:
            echoIndent('var ref'.ucfirst($name).'=1;' . cr(), 2);
            echoIndent($name . '=decoder.decodeBytesForKey("' . $name . '",&ref'.ucfirst($name).')' . cr(), 2);
            break;
        case FlexionsTypes::DATETIME:
            echoIndent($name . '=decoder.decodeObjectForKey("' . $name . '") as? ' . $nativeType . '' . cr(), 2);
            break;
        case FlexionsTypes::URL:
            echoIndent($name . '=decoder.decodeObjectForKey("' . $name . '") as? ' . $nativeType . '' . cr(), 2);
            break;
        case FlexionsTypes::NOT_SUPPORTED:
            echoIndent('//'.$name .'is not supported' . cr(), 2);
            break;
    }
}
?>
    }

    func encodeWithCoder(aCoder: NSCoder) {
<?php
// NSCoding support
while ($d->iterateOnProperties() === true) {
    $property = $d->getProperty();
    $name = $property->name;
    $flexionsType = $property->type;
    $nativeType = FlexionsSwiftLang::nativeTypeFor($flexionsType);
    switch ($flexionsType) {
        case FlexionsTypes::STRING:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeObject('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::INTEGER:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeInteger('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::BOOLEAN:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeBool('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::OBJECT:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeObject('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::COLLECTION:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeObject('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::ENUM:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeObject("\('.$name.')",forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::FILE:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeObject('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::FLOAT:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeFloat('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::DOUBLE:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeDouble('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::BYTE:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeBytes(&self.'.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::DATETIME:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeObject('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::URL:
            echoIndent('if let '.$name.' = self.'.$name.' {'.cr(), 2);
            echoIndent('aCoder.encodeObject('.$name.',forKey:"'. $name .'")' . cr(), 3);
            echoIndent('}'.cr(), 2);
            break;
        case FlexionsTypes::NOT_SUPPORTED:
            echoIndent('//'.$name .'is not supported' . cr(), 2);
            break;
    }
}
?>
    }

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init()
        mapping(map)
    }

    func mapping(map: Map) {
<?php
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    echoIndent($name . ' <- map["' . $name . '"]' . cr(), 2);
}
?>
    }
}

// MARK: - Collection of <?php echo ucfirst($d->name)?>

class CollectionOf<?php echo ucfirst($d->name)?> : NSObject,NSCoding,Mappable{

    var items:[<?php echo ucfirst($d->name)?>]?

    override init(){}

    // MARK: NSCoding

    required init(coder decoder: NSCoder) {
        items=decoder.decodeObjectForKey("items") as? [<?php echo ucfirst($d->name)?>]
    }

    func encodeWithCoder(aCoder: NSCoder) {
        if let items = self.items {
            aCoder.encodeObject(items,forKey:"items")
        }
    }

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init()
        mapping(map)
    }

    func mapping(map: Map) {
        items <- map["items"]
    }
}

<?php /*<- END OF TEMPLATE */?>