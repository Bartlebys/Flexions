<?php


include FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/GenerativeHelperForSwift.class.php';

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


@class <?php echo ucfirst($d->name)?>:NSObject,NSCoding,Mappable{
<?php
// You can distinguish the first, and last property
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    if($d->firstProperty()){
        echoIndent(cr(),0);
    }
    //@todo typed collection
    //@todo CREATE SWIFT TOOLS SET WITH TYPE MAPPING
    //@todo create a notion of transformer for NSURL support for example
    echoIndent('var ' . $name .':'.ucfirst($property->type). '?' . cr(), 1);
    if($d->lastProperty()){
        echoIndent(cr(),0);
    }
}?>

    // MARK: NSCoding

    required init(coder decoder: NSCoder!) {
        //array = decoder.decodeObjectForKey("array") as? [String]
    }

    func encodeWithCoder(aCoder: NSCoder!) {
        aCoder.encodeObject(self.array, forKey: "array")
    }

    // MARK: Mappable

    required init?(map: Map) {
        mapping(map)
    }

    func mapping(_ map: Map) {
<?php
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    echoIndent($name.'<-["'.$name.'"]'.cr(),2);
}?>
    }


}<?php /*<- END OF TEMPLATE */?>