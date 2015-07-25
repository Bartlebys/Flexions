<?php


include FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';

require_once FLEXIONS_ROOT_DIR.'/flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/GenerativeHelperForSwift.class.php';
require_once FLEXIONS_MODULES_DIR.'Languages/FlexionsSwiftLang.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */

if (isset ( $f )) {
    // We determine the file name.
    $f->fileName = 'Facilities.swift';
    // And its package.
    $f->package = 'iOS/swift/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation

class Configuration {

    static let baseUrl=NSURL(string:"<?php echo($d->baseUrl)?>")
}


class HTTPManager {

    static let END_OF_NSURLREQUEST="end_of_request"

    static var deviceIdentifier:String=""

    static var userAgent=""

    static var additionnalHeaders:[String:String]?

    static func mutableRequestWithHeaders(method:Method,url:NSURL)->NSMutableURLRequest{
        var request=NSMutableURLRequest(URL: url)
        request.HTTPMethod=method.rawValue;
        request.addValue(HTTPManager.deviceIdentifier, forHTTPHeaderField: "Device-Identifier")
        request.addValue(HTTPManager.userAgent, forHTTPHeaderField: "User-Agent" )
        request.addValue("application/json", forHTTPHeaderField: "Accept")
        request.addValue("application/json", forHTTPHeaderField: "Content-Type")
        if let additionnalHeaders=HTTPManager.additionnalHeaders{
            for (name,value) in additionnalHeaders{
                request.addValue(value, forHTTPHeaderField:name);
            }
        }
        return request
    }

    static func requestHasEnded(request:NSURLRequest){
         NSNotificationCenter.defaultCenter().postNotificationName(END_OF_NSURLREQUEST, object: request)
    }

}

class HTTPFailure {
    var httpStatusCode:Int!=0
    var message:String!=""
    var infos:[String:AnyObject]!=[:]
}

class AuthorizationFacilities{

    static let AUTHORIZATION_REQUIRED="authorization_required"

    static let AUTHORIZATION_SUCCEEDED="authorization_succeeded"

    static let AUTHORIZATION_FAILED="authorization_failed"

    static let UN_AUTORIZATION_SUCCEEDED="un-authorization_succeeded"

    static let UN_AUTORIZATION_FAILED="un-authorization_failed"


    static func authorizationRequired(message:String)->(){
        NSNotificationCenter.defaultCenter().postNotificationName(AUTHORIZATION_REQUIRED, object: message)
    }

    static func authorizationSucceeded(message:String)->(){
        NSNotificationCenter.defaultCenter().postNotificationName(AUTHORIZATION_SUCCEEDED, object: message)
    }

    static func authorizationFailed(message:String)->(){
        NSNotificationCenter.defaultCenter().postNotificationName(AUTHORIZATION_FAILED, object: message)
    }

    static func un_authorizationSucceeded(message:String)->(){
        NSNotificationCenter.defaultCenter().postNotificationName(UN_AUTORIZATION_SUCCEEDED, object: message)
    }

    static func un_authorizationFailed(message:String)->(){
        NSNotificationCenter.defaultCenter().postNotificationName(UN_AUTORIZATION_FAILED, object: message)
    }
}

<?php /*<- END OF TEMPLATE */?>