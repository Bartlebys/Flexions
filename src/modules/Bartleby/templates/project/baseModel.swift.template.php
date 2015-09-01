<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
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
import ObjectMapper

class <?php echo($d->classPrefix.'BaseModel')?> : <?php if ($modelsShouldConformToNSCoding==true){ echo ('NSObject,NSCoding,Mappable{'); } else {echo ('Mappable{');}?>

    // This id is always  created locally and used as primary index by MONGO
    internal var _id:String?

    var UDID:String{
        get{
            self._createUDIDifNecessary()
            return _id!
        }
    }

    private func _createUDIDifNecessary(){
        if _id==nil {
            _id=NSProcessInfo.processInfo().globallyUniqueString
        }
    }



<?php if( $modelsShouldConformToNSCoding ) {

echo('
    // MARK: NSCoding

    override init(){
        super.init()
        self._createUDIDifNecessary()
    }

    required init(coder decoder: NSCoder) {
        _id=decoder.decodeObjectForKey("_id") as? String
         if _id==nil {
            _id=NSProcessInfo.processInfo().globallyUniqueString
        }
    }

    func encodeWithCoder(aCoder: NSCoder) {
        aCoder.encodeObject(_id,forKey:"_id")
    }

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init()
        mapping(map)
    }
    ');
}else{
    echo('
    init(){
        self._createUDIDifNecessary()
    }

    required init?(_ map: Map) {
        mapping(map)
    }

    ');
}
?>

    class func newInstance(map: Map) -> Mappable? {
        return <?php echo($d->classPrefix.'BaseModel')?>(map)
    }


    func mapping(map: Map) {
        _id <- map["_id"]
        if _id==nil {
            _id=NSProcessInfo.processInfo().globallyUniqueString
        }
    }


}
