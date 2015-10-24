<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

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
        echoIndentCR('var ' . $name .':'.$enumTypeName.'?', 1);
    }else if($property->type==FlexionsTypes::COLLECTION){
        echoIndentCR('var ' . $name .':['.ucfirst($property->instanceOf). ']?', 1);
    }else if($property->type==FlexionsTypes::OBJECT){
        echoIndentCR('var ' . $name .':'.ucfirst($property->instanceOf). '?', 1);
    }else{
        $nativeType=FlexionsSwiftLang::nativeTypeFor($property->type);
        if(strpos($nativeType,FlexionsTypes::NOT_SUPPORTED)===false){
            echoIndentCR('var ' . $name .':'.$nativeType. '?', 1);
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

}


    // MARK: -

@objc(<?php echo ucfirst(Pluralization::pluralize($d->name)).'Collection'?>) class <?php echo ucfirst(Pluralization::pluralize($d->name)).'Collection'?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$d); ?>,CollectibleCollection{

    typealias Index=DictionaryIndex<String,<?php echo ucfirst($d->name)?>>

    dynamic var items=Dictionary<String,<?php echo ucfirst($d->name)?>>()

    // MARK: CollectionType (SequenceType,Indexable)

    func generate() -> DictionaryGenerator<String,<?php echo ucfirst($d->name)?>> {
        return items.generate()
    }

    var startIndex:Index{
        return items.startIndex
    }


    var endIndex:Index{
        return items.endIndex
    }

    subscript (idx: Index) -> (String,<?php echo ucfirst($d->name)?>){
        return  items[idx]
    }

    // MARK: Identifiable


    override class var collectionName:String{
        return <?php echo ucfirst($d->name)?>.collectionName
    }

    override var d_collectionName:String{
        return <?php echo ucfirst($d->name)?>.collectionName
    }


    // MARK: Mappable


    override func mapping(map: Map) {
        super.mapping(map)
        items <- map["items"]
    }

    // MARK: Facilities

    func add(object:<?php echo ucfirst($d->name)?>){
        items[object.UDID]=object
    }

    func remove(object:<?php echo ucfirst($d->name)?>){
        if let idx=items.indexForKey(object.UDID){
            items.removeAtIndex(idx)
        }
    }

    func objectWithUDID(UDID:String)-><?php echo ucfirst($d->name)?>?{
        return items[UDID]
    }

}
<?php /*<- END OF TEMPLATE */?>