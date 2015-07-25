<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

require_once FLEXIONS_SOURCE_DIR . '/SharedSwagger.php';


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
    echoIndent('class ' . $d->class . 'Parameters : NSObject,Mappable {'.cr().cr(), 0);
    while ($d->iterateOnParameters() === true) {
        $parameter = $d->getParameter();
        $name = $parameter->name;
        if(!$d->parameterIsInPath($name)){
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
            }
        }
    }
    echo ("
    override init(){}

    // MARK: Mappable

    required init?(_ map: Map) {
        super.init()
        mapping(map)
    }

    func mapping(map: Map) {
    ");

    while ( $d ->iterateOnParameters() === true ) {
        $property = $d->getParameter();
        $name = $property->name;
        if(!$d->parameterIsInPath($name)){
            echoIndent($name . ' <- map["' . $name . '"]', 1);
            if (!$d->lastParameter()) {
                echoIndent(cr(),0);
            }
        }
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
        echoIndent($pathVariable.':String,'.cr(),$pathVCounter==0?0:6);
        $pathVCounter++;
    }
}
?>
<?php
$successP = $d->getSuccessResponse();
$successTypeString = '';

if ($successP->type == FlexionsTypes::COLLECTION) {
    $successTypeString ='result:'.'[' . ucfirst($successP->instanceOf) . ']';
} else if ($successP->type == FlexionsTypes::OBJECT) {
    $successTypeString = 'result:'.ucfirst($successP->instanceOf);
} else if ($successP->type == FlexionsTypes::DICTIONARY) {
    $successTypeString = 'result:'.'[String:Any]';
}else {
    $nativeType = FlexionsSwiftLang::nativeTypeFor($successP->type);
    $successTypeString='result:'.$nativeType;
}
if ($d->containsParametersOutOfPath()) {
    echoIndent('parameters:' . $d->class . 'Parameters,' . cr(), $pathVCounter>0?6:0);
    echoIndent('sucessHandler success:(' . $successTypeString . ')->(),'.cr(), 6);
} else {
    echoIndent('sucessHandler success:(' . $successTypeString . ')->(),'.cr(), $pathVCounter>0?6:0);
}
echoIndent('failureHandler failure:(result:HTTPFailure)->()){'.cr(), 6);
// We want to inject the path variable into the
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path=$d->path;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        $path=str_ireplace('{'.$pathVariable.'}','\('.$pathVariable.')',$path);
    }
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
$responseBlock=stringIndent('//OK'.cr(),0);
$errorsResponseBlock='//Errors
                                        var f=HTTPFailure()
                                        f.
                                        failure(result: ["statusCode":response?.statusCode,"error":e.description])';


// We need to parse the responses.

foreach ($d->responses as $rank=>$responsePropertyRepresentation ) {
        /* @var  $responsePropertyRepresentation PropertyRepresentation */
        $code=$responsePropertyRepresentation->name;
        if (strpos($code,'2')===0){
            // It is a status code 2XX
            if (isset ($responsePropertyRepresentation->instanceOf)) {
                $classForResponse=$responsePropertyRepresentation->instanceOf;
                $responseBlock .= stringIndent('//' . $responsePropertyRepresentation->type . " " . $responsePropertyRepresentation->instanceOf.cr(), 10);
                $responseBlock .= stringIndent('if let JSONString = responseObject as? String {'.cr(), 10);
                    $responseBlock .= stringIndent('if let instance = Mapper < '.$classForResponse.'>() . map(JSONString){success(result: instance);'.cr(), 11);
                        $responseBlock .= stringIndent('success(result: instance);'.cr(), 12);
                    $responseBlock .= stringIndent('}else{'.cr(), 11);
                    $responseBlock .= stringIndent('}'.cr(), 11);
                //failure(result: ["message":"deserialization failure","json":JSONString])
                $responseBlock .= stringIndent('}'.cr(), 10);
            }
     }
}

$block="                            ".($d->containsParametersOutOfPath()?"var dictionary:[String:AnyObject]?=Mapper().toJSON(parameters)":"var dictionary:[String:AnyObject]=[:]")."
                                var urlRequest=HTTPManager.mutableRequestWithHeaders(Method.".$d->httpMethod.", url: pathURL)
                                var r:Request=request(ParameterEncoding.URL.encode(urlRequest, parameters: dictionary).0)
                                r.responseJSON(options: NSJSONReadingOptions.AllowFragments, completionHandler: { (request:NSURLRequest, response:NSHTTPURLResponse?, responseObject:AnyObject?,error:NSError?) -> Void in
                                    HTTPManager.requestHasEnded(request)
                                    if let e=error {
                                         ".$errorsResponseBlock."
                                    }else{
                                         ".$responseBlock."
                                    }
                                })
";

echoIndent($block,0);

if ($d->httpMethod == "GET") {
}
if ($d->httpMethod == "POST") {

}
if ($d->httpMethod == "PUT") {

}
if ($d->httpMethod == "DELETE") {

}

echoIndent("} else { ".cr(),7);
echoIndent('failure(result: ["Error" :"invalid pathURL for path:'.$path.'"])'.cr(),8);
echoIndent("}".cr(),7);
?>


    }
}

<?php /*<- END OF TEMPLATE */ ?>