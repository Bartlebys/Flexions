<?php
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation */

if (isset ($f)) {
    $f->fileName = $d->class . '.swift';
    $f->package = 'iOS/swift/endpoints/';
}


/* TEMPLATES STARTS HERE -> */?>
<?php

echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
import Alamofire
import ObjectMapper

<?php

//////////////////////////////
/// START OF PARAMETER MODEL
/////////////////////////////


// We generate the parameter class if there is a least one parameter.
if ($d->containsParametersOutOfPath()) {
    echoIndentCR('class ' . $d->class . 'Parameters : '. $f->prefix.'BaseModel'.' {', 0);
    while ($d->iterateOnParameters() === true) {
        $parameter = $d->getParameter();
        $name = $parameter->name;
        echoIndentCR('// ' .$parameter->description. cr(), 1);
        if ($d->firstParameter()) {
        }
        if($parameter->type==FlexionsTypes::ENUM) {
            $enumTypeName = $d->name . ucfirst($name);
            echoIndentCR('enum ' . $enumTypeName . ' : ' . ucfirst($parameter->instanceOf) . '{', 1);
            foreach ($parameter->enumerations as $element) {
                if ($parameter->instanceOf == FlexionsTypes::STRING) {
                    echoIndentCR('case ' . ucfirst($element) . ' = "' . $element . '"' , 2);
                } else {
                    echoIndentCR('case ' . ucfirst($element) . ' = ' . $element . '' , 2);
                }
            }
            echoIndentCR('}' , 1);
            echoIndentCR('var ' . $name . ':' . $enumTypeName . '?' , 1);
        }else if ($parameter->type == FlexionsTypes::COLLECTION) {
            echoIndentCR('var ' . $name . ':[' . ucfirst($parameter->instanceOf) . ']?', 1);
        } else if ($parameter->type == FlexionsTypes::OBJECT) {
            echoIndentCR('var ' . $name . ':' . ucfirst($parameter->instanceOf) . '?', 1);
        } else {
            $nativeType = FlexionsSwiftLang::nativeTypeFor($parameter->type);
            if (strpos($nativeType, FlexionsTypes::NOT_SUPPORTED) === false) {
                echoIndentCR('var ' . $name . ':' . $nativeType . '?', 1);
            } else {
                echoIndentCR('var ' . $name . ':Not_Supported = Not_Supported//' . ucfirst($parameter->type), 1);
            }
        }
    }

    echo ('
    override init(){
        super.init()
    }
');
    if( $modelsShouldConformToNSCoding ) {

    echo('
     // MARK: NSCoding

    required init(coder decoder: NSCoder) {
        super.init(coder: decoder)'.cr());
        GenerativeHelperForSwift::echoBodyOfInitWithCoder($d,2);
        echo('
    }

    override func encodeWithCoder(aCoder: NSCoder) {
        super.encodeWithCoder(aCoder)'.cr());
    GenerativeHelperForSwift::echoBodyOfEncodeWithCoder($d,2);
        echo('
    }');

    }

    echo('

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init(map)
        mapping(map)
    }

     override static func newInstance(map: Map) -> Mappable?{
        return '.$d->class.'Parameters(map)
    }

    override func mapping(map: Map) {
        super.mapping(map)'.cr());

    while ( $d ->iterateOnParameters() === true ) {
        $property = $d->getParameter();
        $name = $property->name;
        echoIndentCR($name . ' <- map["' . $name . '"]', 2);
    }
    echo ("
    }
}
");
}


///////////////////////////////////
/// START OF END POINT EXEC CLASS
//////////////////////////////////


?>

class <?php echo $d->class; ?>{

    static func execute(<?php
// We want to inject the path variable into the
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$pathVCounter=0;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        // Suspended
        echoIndentCR($pathVariable.':String,',$pathVCounter==0?0:6);
        $pathVCounter++;
    }
}
?>
<?php
$successP = $d->getSuccessResponse();
$successTypeString = '';
if ($successP->type == FlexionsTypes::COLLECTION) {
    $successTypeString = Pluralization::pluralize($successP->instanceOf).'Collection';//'CollectionOf' . ucfirst($successP->instanceOf) . '';
} else if ($successP->type == FlexionsTypes::OBJECT) {
    $successTypeString = ucfirst($successP->instanceOf);
} else if ($successP->type == FlexionsTypes::DICTIONARY) {
    $successTypeString = '[String:Any]';
}else {
    $nativeType = FlexionsSwiftLang::nativeTypeFor($successP->type);
    if($nativeType==FlexionsTypes::NOT_SUPPORTED){
        $successTypeString='';
    }else{
        $successTypeString=$nativeType;
    }
}

if($successP->isGeneratedType==true){
    $successParameterName=lcfirst($h->ucFirstRemovePrefixFromString($successTypeString));
}else{
    $successParameterName='result';
}
$resultSuccessTypeString=$successTypeString!=''?$successParameterName.':'.$successTypeString:'';

if ($d->containsParametersOutOfPath()) {
    echoIndentCR('parameters:' . $d->class . 'Parameters,' , $pathVCounter>0?6:0);
    echoIndentCR('sucessHandler success:(' . $resultSuccessTypeString . ')->(),', 6);
} else {
    echoIndentCR('sucessHandler success:(' . $resultSuccessTypeString . ')->(),', $pathVCounter>0?6:0);
}

// We want to inject the path variable
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path=$d->path;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        $path=str_ireplace('{'.$pathVariable.'}','\('.$pathVariable.')',$path);
    }
}

echoIndentCR('failureHandler failure:(result:HTTPFailure)->()){', 6);
echoIndentCR('');
$authenticationRequired=false;

if( isset($d->security) && $d->security->getRelation()==RelationToPermission::REQUIRES){
    $authenticationRequired=true;
}

    $parametersString='';
    if ($d->containsParametersOutOfPath()) {
        $parametersString='[';
        while ($d->iterateOnParameters() === true) {
            $parameter = $d->getParameter();
            $name = $parameter->name;
            $parametersString.='"'.$name.'":parameters.'.$name;
            if($parameter->type==FlexionsTypes::ENUM) {
                $parametersString.='?.rawValue';
            }
            if (!$d->lastParameter()){
                $parametersString.=',';
            }
        }
        $parametersString.=']';
    }
// We need to parse the responses.

$status2XXHasBeenDefined=false;
$successMicroBlock=NULL;
ksort($d->responses); // We sort the key by codes
foreach ($d->responses as $rank=>$responsePropertyRepresentation ) {
    /* @var  $responsePropertyRepresentation PropertyRepresentation */
    $code = $responsePropertyRepresentation->name;
    if (strpos($code, '2') === 0) {
        // THERE SHOULD HAVE ONE 2XX HTTP CODE per endpoint
        // THE OTHER WILL CURRENTLY BE IGNORED
        // DEFINE AT LEAST ONE IF YOU WANT TO DETERMINE THE RESPONSE MODEL
        // ELSE IT WILL BE INFERRED
        // YOU CAN CHECK $successTypeString TO UNDERSTAND THE INFERENCE MECANISM
        if ($status2XXHasBeenDefined == false) {
            $status2XXHasBeenDefined = true;

            if($responsePropertyRepresentation->isGeneratedType) {
                // We wanna cast the result if there is one specified
                $successMicroBlock = stringIndent(
'
if let instance = Mapper <' . $successTypeString . '>() . map(result.value){
    success(' . $successParameterName . ': instance)
  }else{
    var f=HTTPFailure()
    f.relatedURL=request?.URL
    f.httpStatusCode=statusCode
    f.message="Deserialization issue"
    f.infos=response
    failure(result: f)
}',5);
            }
        }
    }
}

if( ! isset($successMicroBlock)){
    $successMicroBlock =stringIndent(
        '
if let r=result.value as? ' . $successTypeString . '{
    success(' . $successParameterName . ':r)
 }else{
    var f=HTTPFailure()
    f.relatedURL=request?.URL
    f.httpStatusCode=statusCode
    f.message="Deserialization issue"
    f.infos=response
    failure(result: f)
}',5);
}

$parameterEncodingString='JSON';
if($d->httpMethod=='GET'){
    $parameterEncodingString='URL';
}

if($authenticationRequired) {
    // We could distinguish the permission context.

    echoIndentCR(
'
if !HTTPManager.isAuthenticated {
    var f=HTTPFailure()
    f.message="Authentication required"
    AuthorizationFacilities.authorizationRequired("for '.$d->class.'")
    failure(result: f)
}else{'
        ,3);
}
    echoIndentCR(
'
if  let pathURL=Configuration.baseUrl?.URLByAppendingPathComponent("'.$path.'") {
     '.(($d->containsParametersOutOfPath()?'let dictionary:[String:AnyObject]?=Mapper().toJSON(parameters)':'let dictionary:[String:AnyObject]=[:]')).'
    let urlRequest=HTTPManager.mutableRequestWithHeaders(Method.'.$d->httpMethod.', url: pathURL)
    let r:Request=request(ParameterEncoding.'.$parameterEncodingString.'.encode(urlRequest, parameters: dictionary).0)
    r.responseJSON(completionHandler: { (request, response, result) -> Void in
        HTTPManager.requestHasEnded(request!)
        if result.isFailure {
            var f=HTTPFailure()
            if let r = response{
                f.relatedURL=request?.URL
                f.httpStatusCode=r.statusCode
                f.message=NSHTTPURLResponse.localizedStringForStatusCode( f.httpStatusCode)
                f.infos=r
            }else{
                f.relatedURL=request?.URL
                f.message="Response is void"
            }
            failure(result: f)
        }else{
            if let statusCode=response?.statusCode {
                if 200...299 ~= statusCode {
'.$successMicroBlock.'
                }else{
                    // Bartlby does not currenlty discriminate status codes 100 & 101
                    // and treats any status code >= 300 the same way
                    // because we consider that failures differentiations could be done by the caller.
                    var f=HTTPFailure()
                    f.relatedURL=request?.URL
                    f.httpStatusCode=statusCode
                    f.message=NSHTTPURLResponse.localizedStringForStatusCode(statusCode)
                    f.infos=response
                    failure(result: f)
                }
            }
        }
    })
    } else {
        var f=HTTPFailure()
        f.message="invalid pathURL for path:'.$path.'"
    }
   }
}',4);
if($authenticationRequired) {
    echoIndentCR('}',1);
}

?><?php /*<- END OF TEMPLATE */ ?>