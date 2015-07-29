<?php

require_once FLEXIONS_MODULES_DIR . '/ApiStackSwiftPhpMongo/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = $d->classPrefix.'BaseModel.swift';
    // And its package.
    $f->package = 'iOS/swift/models/';

}

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation

class <?php echo($d->classPrefix.'BaseModel')?>: NSObject,NSCoding,Mappable{

    override init(){}

    // MARK: NSCoding

    required init(coder decoder: NSCoder) {}

    func encodeWithCoder(aCoder: NSCoder) {}

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init()
        mapping(map)
    }

    class func newInstance() -> Mappable {
        return <?php echo($d->classPrefix.'BaseModel')?>()
    }


    func mapping(map: Map) {}


}

