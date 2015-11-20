<?php
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation */

if (isset ($f)) {
    $f->fileName = $d->class . '.swift';
    $f->package = 'iOS/swift/endpoints/';
}

// Exclusion

$shouldBeExcluded = false;
$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $d->class);

if (isset($excludeActionsWith)) {
    $exclusion = $excludeActionsWith;
}
foreach ($exclusion as $exclusionString) {
    if (strpos($exclusionName, $exclusionString) !== false) {
        return NULL; // We return null
    }
}


/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
import Alamofire
import ObjectMapper

<?php

//////////////////////////////
/// START OF PARAMETER MODEL
/////////////////////////////


// We generate the parameter class if there is a least one parameter.
if ($d->containsParametersOutOfPath()) {
    echoIndentCR('@objc('.$d->class.'Parameters) class ' . $d->class . 'Parameters : '.GenerativeHelperForSwift::getBaseClass($f,$d).' {', 0);
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
    required init(){
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
@objc(<?php echo $d->class ?>) class <?php echo $d->class; ?> : <?php echo GenerativeHelperForSwift::getBaseClass($f,$d) ?>{

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
    $successTypeString = '['.$successP->instanceOf.']';
} else if ($successP->type == FlexionsTypes::OBJECT) {
    $successTypeString = ucfirst($successP->instanceOf);
} else if ($successP->type == FlexionsTypes::DICTIONARY) {
    $successTypeString = 'Dictionary<String, AnyObject>';
}else {
    $nativeType = FlexionsSwiftLang::nativeTypeFor($successP->type);
    if($nativeType==FlexionsTypes::NOT_SUPPORTED){
        $successTypeString='';
    }else{
        $successTypeString=$nativeType;
    }
}

$resultSuccessIsACollection=($successP->type == FlexionsTypes::COLLECTION);


if($resultSuccessIsACollection){
    $successParameterName=lcfirst($h->ucFirstRemovePrefixFromString($successP->instanceOf));
}else{
    if($successP->isGeneratedType==true){
        $successParameterName=lcfirst($h->ucFirstRemovePrefixFromString($successTypeString));
    }else{
        $successParameterName='result';
    }
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
''.(($resultSuccessIsACollection)?
    'if let instance = Mapper <' . $successP->instanceOf . '>().mapArray(result.value){
    '
    :'if let instance = Mapper <' . $successTypeString . '>().map(result.value){
    ')
.'
    success(' . $successParameterName . ': instance)
  }else{
    let f=HTTPFailure()
    f.relatedURL=request?.URL
    f.httpStatusCode=statusCode
    f.message="Deserialization issue\n\(result.value)"
    f.infos=response
    failure(result: f)
}',5);
            }
        }
    }
}

if( ! isset($successMicroBlock)){

    if($successTypeString==''){
        // there is no return type
        $successMicroBlock =stringIndentCR('success()',4);
    }else{
        $successMicroBlock =stringIndent(
            '
if let r=result.value as? ' . $successTypeString . '{

    success(' . $successParameterName . ':r)
 }else{
    let f=HTTPFailure()
    f.relatedURL=request?.URL
    f.httpStatusCode=statusCode
    f.message="Deserialization issue\n\(result.value)"
    f.infos=response
    failure(result: f)
}',2);
    }


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
    let f=HTTPFailure()
    f.message="Authentication required"
    AuthorizationFacilities.authorizationRequired("for '.$d->class.'")
    failure(result: f)
}else{'
        ,3);
}
    echoIndentCR(
'
    let pathURL=Configuration.baseUrl.URLByAppendingPathComponent("'.$path.'")
    '.(($d->containsParametersOutOfPath()?'let dictionary:Dictionary<String, AnyObject>?=Mapper().toJSON(parameters)':'let dictionary:Dictionary<String, AnyObject>=[:]')).'
    let urlRequest=HTTPManager.mutableRequestWithHeaders(Method.'.$d->httpMethod.', url: pathURL)
    let r:Request=request(ParameterEncoding.'.$parameterEncodingString.'.encode(urlRequest, parameters: dictionary).0)
    r.'.(($successTypeString=='')?'responseString':'responseJSON').'{ response in

        // ALAMOFIRE 3.0 migration in progress

	    let request=response.request
        let result=response.result
        let response=response.response

        if result.isFailure {
            let f=HTTPFailure()
            if let r = response{
                f.relatedURL=request?.URL
                f.httpStatusCode=r.statusCode
                f.message="\(result.value)"
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
                    let f=HTTPFailure()
                    f.relatedURL=request?.URL
                    f.httpStatusCode=statusCode
                    f.message="\(result.value)"
                    f.infos=response
                    failure(result: f)
                }
            }
        }
      }
   }
}',4);
if($authenticationRequired) {
    echoIndentCR('}',1);
}

?><?php /*<- END OF TEMPLATE */ ?>