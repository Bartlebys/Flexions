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

    // This id can be created locally or be created server side as a Mongo ID
    internal var _UDID:String?
    private var _mongoID:[String:String]=[:]

    var UDID:String{
        get{
            if _UDID==nil {
                _UDID=NSProcessInfo.processInfo().globallyUniqueString
            }
            return _UDID!
        }
    }


    override init(){
    }

    // MARK: NSCoding

    required init(coder decoder: NSCoder) {
        _mongoID=decoder.decodeObjectForKey("_id") as! [String:String]
        _UDID = _mongoID["$id"]
    }

    func encodeWithCoder(aCoder: NSCoder) {
        aCoder.encodeObject(_mongoID,forKey:"_id")
    }

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init()
        mapping(map)
    }

    class func newInstance() -> Mappable {
        return <?php echo($d->classPrefix.'BaseModel')?>()
    }


    func mapping(map: Map) {
        _mongoID <- map["_id"]
        _UDID = _mongoID["$id"]
    }


}
