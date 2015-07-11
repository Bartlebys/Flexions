<?php

include FLEXIONS_SOURCE_DIR.'/SharedMPS.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/LocalSwiftTools.class.php';

if (isset ( $f )) {
    $f->fileName = LocalSwiftTools::getCurrentClassNameWithPrefix($d).'.swift';
    $f->package = 'iOS/swift/models/';
}

echoIndent('@class '.ucfirst($d->name).':Mappable{'.cr(),0);
/* @var $d EntityRepresentation */
// You can distinguish the first, and last property
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    if($d->firstProperty()){
        echoIndent(cr(),0);
    }
    //@todo HEADER generator
    //@todo typed collection
    //@todo CREATE SWIFT TOOLS SET WITH TYPE MAPPING
    //@todo create a notion of transformer for NSURL support for example
    echoIndent('var ' . $name .':'.ucfirst($property->type). '?' . cr(), 1);
    if($d->lastProperty()){
        echoIndent(cr(),0);
    }
}


echoIndent('// MARK: Mappable'.cr(),1);
echoIndent('required init?(map: Map) {'.cr(),1);
echoIndent('mapping(map)'.cr(),2);
echoIndent('}'.cr(),1);
echoIndent('func mapping(_ map: Map) {'.cr(),1);
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name = $property->name;
    echoIndent($name.'<-["'.$name.'"]'.cr(),2);
}

echoIndent('}'.cr(),1);
echoIndent(cr().'}');

?>