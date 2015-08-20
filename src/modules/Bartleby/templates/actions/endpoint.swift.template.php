<?php
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation */

if (isset ($f)) {
    $f->fileName = $d->class . '.swift';
    $f->package = 'iOS/swift/endpoints/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation

<?php
// We generate the parameter class if there is a least one parameter.
if ($d->containsParametersOutOfPath()) {
    echoIndent('class ' . $d->class . 'Parameters : '. $f->prefix.'BaseModel'.' {'.cr().cr(), 0);
    while ($d->iterateOnParameters() === true) {
        $parameter = $d->getParameter();
        $name = $parameter->name;

        echoIndent('// ' .$parameter->description. cr(), 1);
        if ($d->firstParameter()) {
        }
        if($parameter->type==FlexionsTypes::ENUM) {
            $enumTypeName = $d->name . ucfirst($name);
            echoIndent('enum ' . $enumTypeName . ' : ' . ucfirst($parameter->instanceOf) . '{' . cr(), 1);
            foreach ($parameter->enumerations as $element) {
                if ($parameter->instanceOf == FlexionsTypes::STRING) {
                    echoIndent('case ' . ucfirst($element) . ' = "' . $element . '"' . cr(), 2);
                } else {
                    echoIndent('case ' . ucfirst($element) . ' = ' . $element . '' . cr(), 2);
                }
            }
            echoIndent('}' . cr(), 1);
            echoIndent('var ' . $name . ':' . $enumTypeName . '?' . cr(), 1);
        }else if ($parameter->type == FlexionsTypes::COLLECTION) {
            echoIndent('var ' . $name . ':[' . ucfirst($parameter->instanceOf) . ']?' . cr(), 1);
        } else if ($parameter->type == FlexionsTypes::OBJECT) {
            echoIndent('var ' . $name . ':' . ucfirst($parameter->instanceOf) . '?' . cr(), 1);
        } else {
            $nativeType = FlexionsSwiftLang::nativeTypeFor($parameter->type);
            if (strpos($nativeType, FlexionsTypes::NOT_SUPPORTED) === false) {
                echoIndent('var ' . $name . ':' . $nativeType . '?' . cr(), 1);
            } else {
                echoIndent('var ' . $name . ':Not_Supported = Not_Supported//' . ucfirst($parameter->type) . cr(), 1);
            }
        }
        if ($d->lastParameter()) {
            echoIndent(cr());
        }

    }

    if($d->httpMethod=="PUT"||
        $d->httpMethod=="POST"||
        $d->httpMethod=="DELETE"
    ){
        echoIndent("//You can provide a command that allows to Do/Undo/Redo this action".cr(),1);
        echoIndent("var command:".$h->classPrefix."Command?".cr(),1);
    }
    echo ("

    override init(){
        super.init()
    }

     // MARK: NSCoding

    required init(coder decoder: NSCoder) {
        super.init(coder: decoder)".cr());
    GenerativeHelperForSwift::echoBodyOfInitWithCoder($d,2);
    echo("
     }");
    echo("
    override func encodeWithCoder(aCoder: NSCoder) {
        super.encodeWithCoder(aCoder)".cr());
    GenerativeHelperForSwift::echoBodyOfEncodeWithCoder($d,2);
    echo("    }


    // MARK: Mappable

    required init?(_ map: Map) {
        super.init()
        mapping(map)
    }

    override class func newInstance() -> Mappable {
        return ".$d->class."Parameters()
    }

    override func mapping(map: Map) {
        super.mapping(map)".cr());

    while ( $d ->iterateOnParameters() === true ) {
        $property = $d->getParameter();
        $name = $property->name;
       // if(!$d->parameterIsInPath($name)){
            echoIndent($name . ' <- map["' . $name . '"]', 2);
            if (!$d->lastParameter()) {
                echoIndent(cr(),0);
            }
        //}
    }
    echo ("
    }
}
");
} ?>

class <?php echo $d->class; ?>{

    static func execute(<?php
// We want to inject the path variable into the
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$pathVCounter=0;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        // Suspended
        echoIndent($pathVariable.':String,'.cr(),$pathVCounter==0?0:6);
        $pathVCounter++;
    }
}
?>
<?php
$successP = $d->getSuccessResponse();
$successTypeString = '';
if ($successP->type == FlexionsTypes::COLLECTION) {
    $successTypeString ='CollectionOf' . ucfirst($successP->instanceOf) . '';
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

$resultSuccessTypeString=$successTypeString!=''?'result:'.$successTypeString:'';
if ($d->containsParametersOutOfPath()) {
    echoIndent('parameters:' . $d->class . 'Parameters,' . cr(), $pathVCounter>0?6:0);
    echoIndent('sucessHandler success:(' . $resultSuccessTypeString . ')->(),'.cr(), 6);
} else {
    echoIndent('sucessHandler success:(' . $resultSuccessTypeString . ')->(),'.cr(), $pathVCounter>0?6:0);
}

// We want to inject the path variable
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path=$d->path;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        $path=str_ireplace('{'.$pathVariable.'}','\('.$pathVariable.')',$path);
    }
}

echoIndent('failureHandler failure:(result:HTTPFailure)->()){'.cr(), 6);
$authenticationRequired=false;

if( isset($d->security) && $d->security->getRelation()==RelationToPermission::REQUIRES){
    $authenticationRequired=true;
}

if($authenticationRequired){

    // We could distinguish the permission context.

    echoIndent('if !HTTPManager.isAuthenticated {'.cr(),6);
    echoIndent('var f=HTTPFailure()'.cr(), 7);
    echoIndent('f.message="Authentication required"'.cr(), 7);
    echoIndent('AuthorizationFacilities.authorizationRequired("for '.$d->class.'")'.cr(), 7);
    echoIndent('failure(result: f);'.cr(), 7);
    echoIndent('}else{'.cr(), 6);

}

echoIndent('if  let pathURL=Configuration.baseUrl?.URLByAppendingPathComponent("'.$path.'") {'.cr(),7);
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
$responseBlock=cr();
// We need to parse the responses.
$successHasBeenDefined=false;
foreach ($d->responses as $rank=>$responsePropertyRepresentation ) {
        /* @var  $responsePropertyRepresentation PropertyRepresentation */
        $code=$responsePropertyRepresentation->name;
                if (strpos($code,'2')===0) {
                    // It is a status code 2XX
                    if ($responsePropertyRepresentation->isGeneratedType) {
                        $responseBlock .= stringIndent('if 200...299 ~= statusCode {'. cr(), 11);
                        $responseBlock .= stringIndent('if let JSONString = responseObject as? String {' . cr(), 12);
                        $responseBlock .= stringIndent('if let instance = Mapper <' . $successTypeString . '>() . map(JSONString){' . cr(), 13);
                        $responseBlock .= stringIndent('success(result: instance);' . cr(), 14);
                        $responseBlock .= stringIndent('}else{'.cr(), 13);
                        $responseBlock .= stringIndent('var f=HTTPFailure()'.cr(), 14);
                        $responseBlock .= stringIndent('f.message="Deserialization issue"'.cr(), 14);
                        $responseBlock .= stringIndent('f.infos=responseObject'.cr(), 14);
                        $responseBlock .= stringIndent('failure(result: f)'.cr(), 14);
                        $responseBlock .= stringIndent('}'.cr(), 13);
                        $responseBlock .= stringIndent('}'.cr(), 12);
                        $responseBlock .= stringIndent('}'.cr(), 11);

                    }else{
                        $responseBlock .= stringIndent('if 200...299 ~= statusCode {'. cr(), 11);
                        if( $successTypeString==""){
                            $responseBlock .= stringIndent('success()'. cr(), 12);
                        }else{
                            $responseBlock .= stringIndent('if let r=responseObject as? '.$successTypeString.'{'. cr(), 12);
                            $responseBlock .= stringIndent('success(result:r)'. cr(), 13);
                            $responseBlock .= stringIndent('}else{'.cr(), 12);
                            $responseBlock .= stringIndent('var f=HTTPFailure()'. cr(), 13);
                            $responseBlock .= stringIndent('f.message="Casting error"'. cr(), 13);
                            $responseBlock .= stringIndent('f.infos=responseObject'. cr(), 13);
                            $responseBlock .= stringIndent('failure(result: f)'. cr(), 13);
                            $responseBlock .= stringIndent('}'.cr(), 12);
                        }
                        $responseBlock .= stringIndent('}'.cr(), 11);

                    }
                    $successHasBeenDefined=true;
                }else{
                    // It is not a status 2XX
                    $responseBlock .= stringIndent('if statusCode == '.$responsePropertyRepresentation->name.'{'.cr(), 11);
                    $responseBlock .= stringIndent('var f=HTTPFailure()'.cr(), 12);
                    $responseBlock .= stringIndent('f.message="'.$responsePropertyRepresentation->description.'"'.cr(), 12);
                    $responseBlock .= stringIndent('f.infos=responseObject'.cr(), 12);
                    $responseBlock .= stringIndent('failure(result: f)'.cr(), 12);
                    $responseBlock .= stringIndent('}'.cr(), 11);
                }
}

if($successHasBeenDefined==false){
   // We need to add a success
    $responseBlock .= stringIndent('if 200...299 ~= statusCode {'. cr(), 11);
    if( $successTypeString==""){
        $responseBlock .= stringIndent('success()'. cr(), 12);
    }else{
        $responseBlock .= stringIndent('if let r=responseObject as? '.$successTypeString.'{'. cr(), 12);
        $responseBlock .= stringIndent('success(result:r)'. cr(), 13);
        $responseBlock .= stringIndent('}else{'.cr(), 12);
        $responseBlock .= stringIndent('var f=HTTPFailure()'. cr(), 13);
        $responseBlock .= stringIndent('f.message="Casting error"'. cr(), 13);
        $responseBlock .= stringIndent('f.infos=responseObject'. cr(), 13);
        $responseBlock .= stringIndent('failure(result: f)'. cr(), 13);
        $responseBlock .= stringIndent('}'.cr(), 12);
  }


    $responseBlock .= stringIndent('}'.cr(), 11);
}

$block="                            ".($d->containsParametersOutOfPath()?"var dictionary:[String:AnyObject]?=Mapper().toJSON(parameters)":"var dictionary:[String:AnyObject]=[:]")."
                                var urlRequest=HTTPManager.mutableRequestWithHeaders(Method.".$d->httpMethod.", url: pathURL)
                                var r:Request=request(ParameterEncoding.URL.encode(urlRequest, parameters: dictionary).0)
                                r.responseJSON(options: NSJSONReadingOptions.AllowFragments, completionHandler: { (request:NSURLRequest, response:NSHTTPURLResponse?, responseObject:AnyObject?,error:NSError?) -> Void in
                                    HTTPManager.requestHasEnded(request)
                                    if let e=error {
                                        //Errors
                                        var f=HTTPFailure()
                                        f.httpStatusCode=response?.statusCode
                                        f.message=NSHTTPURLResponse.localizedStringForStatusCode( f.httpStatusCode)
                                        f.infos=responseObject
                                        failure(result: f);
                                    }else{
                                        if let statusCode=response?.statusCode {
                                         ".$responseBlock."
                                        }
                                    }
                                })
";
echoIndent($block,0);
echoIndent("} else { ".cr(),7);
echoIndent('var f=HTTPFailure()'.cr(),8);
echoIndent('f.message="invalid pathURL for path:'.$path.'";'.cr(),8);
echoIndent('failure(result: f)'.cr(),8);
echoIndent("}".cr(),7);
if($authenticationRequired){
    echoIndent("}".cr(),6);
}
?>


    }
}
<?php /*<- END OF TEMPLATE */ ?>