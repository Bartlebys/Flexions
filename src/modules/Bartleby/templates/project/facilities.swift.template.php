<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . 'Languages/FlexionsSwiftLang.php';

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
import Alamofire

class Configuration {

    static let baseUrl=NSURL(string:"<?php echo($d->baseUrl)?>")
}


class HTTPManager {

    static let END_OF_NSURLREQUEST="end_of_request"

    static var deviceIdentifier:String=""

    static var userAgent=""

    static var additionnalHeaders:[String:String]?

    static var isAuthenticated=false

    static func mutableRequestWithHeaders(method:Alamofire.Method,url:NSURL)->NSMutableURLRequest{
        let request=NSMutableURLRequest(URL: url)
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
struct HTTPFailure : CustomStringConvertible {

    var relatedURL:NSURL?
    var httpStatusCode:Int!=0
    var message:String!="Undefined message"
    var infos:AnyObject?

    internal var description: String {
        get{
            return "URL:\(relatedURL)\nHTTP Status Code:\(httpStatusCode)\nMessage:\(message)\nInfos:\(infos)"
        }
    }

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