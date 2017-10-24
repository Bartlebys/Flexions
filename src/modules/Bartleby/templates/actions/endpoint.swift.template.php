<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation */

if (isset ($f)) {
    $f->fileName = $d->class . '.swift';
    $f->package = 'xOS/operations/';
}


/////////////////
// EXCLUSIONS
/////////////////


// IMPORTANT (!)
// We want to use this template on Read operations Only.
// We use CUDS for the other operations
if ($d->httpMethod != 'GET') {
    return NULL;
}

// Exclusion
$exclusionName = str_replace($h->classPrefix, '', $d->class);
if (isset($excludeActionsWith)) {
    foreach ($excludeActionsWith as $exclusionString) {
        if (strpos($exclusionName, $exclusionString) !== false) {
            return NULL; // We return null
        }
    }
}

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

$blockRepresentation=$d;
$actionClassName=ucfirst($d->class);

// EXPOSED
$exposedBlock='';
if ($modelsShouldConformToExposed){
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>false]);
    $exposedBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Exposed.swift.block.php');
}

// MAPPABLE
$mappableBlock='';
if ($modelsShouldConformToMappable){
    $mappableblockEndContent='';
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>false,'mappableblockEndContent'=>$mappableblockEndContent]);
    $mappableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Mappable.swift.block.php');
}

// NSSECURECODING
$secureCodingBlock='';
if($modelsShouldConformToNSSecureCoding) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>false,'decodingblockEndContent'=>'','encodingblockEndContent'=>'']);
    $secureCodingBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/NSSecureCoding.swift.block.php');
}

// CODABLE
$codableBlock='';
if($modelsShouldConformToCodable) {
    // We define the context for the block
    Registry::Instance()->defineVariables(['blockRepresentation'=>$blockRepresentation,'isBaseObject'=>false,'decodableblockEndContent'=>'','encodableblockEndContent'=>'']);
    $codableBlock=stringFromFile(FLEXIONS_MODULES_DIR.'/Bartleby/templates/blocks/Codable.swift.block.php');
}


// Include block

$includeBlock='';
if (isset($isIncludeInBartlebysCommons) && $isIncludeInBartlebysCommons==true){
    $includeBlock .= stringIndent("import Alamofire",1);
}else{
    $includeBlock .= stringIndent("import Alamofire",1);
    $includeBlock .= stringIndent("import BartlebyKit",1);
}


// Metrics Block
$metricsBlock ='let metrics=Metrics()
metrics.httpContext=context
metrics.operationName="'.$actionClassName.'"
metrics.latency=timeline.latency
metrics.requestDuration=timeline.requestDuration
metrics.serializationDuration=timeline.serializationDuration
metrics.totalDuration=timeline.totalDuration';
$metricsBlock .= cr().'document.report(metrics)';
$metricsBlock = stringIndent($metricsBlock, 5);
$metricsBlock = cr() . $metricsBlock;



/////////////////////////
//  TEMPLATE
/////////////////////////

if ($d->class == "DeleteNodes") {
    $a = 1;
}

?>

<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>

import Foundation
#if !USE_EMBEDDED_MODULES
<?php echo $includeBlock ?>
#endif
<?php

//////////////////////////////
/// START OF PARAMETER MODEL
/////////////////////////////

// We generate the parameter class if there is a least one parameter.
if ($d->containsParametersOutOfPath()) {
    echoIndent('@objc public class ' . $d->class . 'Parameters : ' . GenerativeHelperForSwift::getBaseClass($d) . ' {', 0);

    // Universal type support
    echoIndent('');
    echoIndent('// Universal type support',1);
    echoIndent('override open class func typeName() -> String {',1);
        echoIndent(' return "'.$d->class . 'Parameters"',2);
    echoIndent('}',1);
    while ($d->iterateOnParameters() === true) {
        $parameter = $d->getParameter();
        $name = $parameter->name;

        if (!$d->parameterIsInPath($name)) {
            echoIndent('// ' . $parameter->description . cr(), 1);
            if ($d->firstParameter()) {
            }
            if ($parameter->type == FlexionsTypes::ENUM) {
                $enumTypeName = $d->name . ucfirst($name);
                echoIndent('public enum ' . $enumTypeName . ' : ' . ucfirst($parameter->instanceOf) . '{', 1);
                foreach ($parameter->enumerations as $element) {
                    if ($parameter->instanceOf == FlexionsTypes::STRING) {
                        echoIndent('case ' . ucfirst($element) . ' = "' . $element . '"', 2);
                    } else {
                        echoIndent('case ' . ucfirst($element) . ' = ' . $element . '', 2);
                    }
                }
                echoIndent('}', 1);
                echoIndent('public var ' . $name . ':' . $enumTypeName . '?', 1);
            } else if ($parameter->type == FlexionsTypes::COLLECTION) {
                echoIndent('public var ' . $name . ':[' . ucfirst($parameter->instanceOf) . ']?', 1);
            } else if ($parameter->type == FlexionsTypes::OBJECT) {
                echoIndent('public var ' . $name . ':' . ucfirst($parameter->instanceOf) . '?', 1);
            } else {
                $nativeType = FlexionsSwiftLang::nativeTypeFor($parameter->type);
                if (strpos($nativeType, FlexionsTypes::NOT_SUPPORTED) === false) {
                    echoIndent('public var ' . $name . ':' . $nativeType . '?', 1);
                } else {
                    echoIndent('public var ' . $name . ':Not_Supported = Not_Supported//' . ucfirst($parameter->type), 1);
                }
            }
        }
    }
    echo('
    required public init(){
        super.init()
    }
');
    echo $exposedBlock;
    echo $mappableBlock;
    echo $secureCodingBlock;
    echo $codableBlock;
    echo '}'.cr();
}
?>

<?php

///////////////////////////////////
/// START OF END POINT EXEC CLASS
//////////////////////////////////

?>
@objc(<?php echo $d->class; ?>) open class <?php echo $d->class; ?> : <?php echo GenerativeHelperForSwift::getBaseClass($d) ?>{

    // Universal type support
    override open class func typeName() -> String {
           return "<?php echo $d->class; ?>"
    }


    public static func execute(<?php
// We want to inject the path variable into the
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$pathVCounter=0;
$hasRegistryUID= in_array('documentUID',$pathVariables);
if (!$hasRegistryUID){
    echoIndent('from documentUID:String,',$pathVCounter>0);
}

if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        if ($pathVariable=='documentUID'){
            $hasRegistryUID=true;
        }
        // Suspended
        echoIndent($pathVariable.':String,',6);
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
    $successTypeString = 'Dictionary<String, Any>';
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
    $successParameterName= Pluralization::pluralize(lcfirst($h->ucFirstRemovePrefixFromString($successP->instanceOf)));
}else{
    if($successP->isGeneratedType==true){
        $successParameterName=lcfirst($h->ucFirstRemovePrefixFromString($successTypeString));
    }else{
        $successParameterName='result';
    }
}


$resultSuccessTypeString = ($successTypeString != '' ? $successParameterName . ':' . $successTypeString : '');
if ($d->containsParametersOutOfPath()) {
    echoIndent('parameters:' . $d->class . 'Parameters,' , 6);
    echoIndent('sucessHandler success:@escaping(_ ' . $resultSuccessTypeString . ')->(),', 6);
} else {
    echoIndent('sucessHandler success:@escaping(_ ' . $resultSuccessTypeString . ')->(),', 6);
}

// We want to inject the path variable
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path= (strpos($d->path,'/')!==false) ? substr($d->path,1):$d->path;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        $path=str_ireplace('{'.$pathVariable.'}','\('.$pathVariable.')',$path);
    }
}
echoIndent('failureHandler failure:@escaping(_ context:HTTPContext)->()){', 6);
echoIndent('');
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
''.(

    ($resultSuccessIsACollection)?
'
                        do{
                            if let data = response.data{
                                let instance = try JSON.decoder.decode([' . $successP->instanceOf . '].self,from:data)
                                success(instance)'
                        :
'
                       do{
                            if let data = response.data{
                                let instance = try JSON.decoder.decode(' . $successTypeString . '.self,from:data)
                                success(instance)'
)
.'
                              }else{
                                throw BartlebyOperationError.dataNotFound
                              }
                            }catch{
                                let failureReaction =  Reaction.dispatchAdaptiveMessage(
                                    context: context,
                                    title:"\(error)",
                                    body: "\(String(describing: result.value))\n\(#file)\n\(#function)\nhttp Status code: (\(statusCode))",
                                    transmit: { (selectedIndex) -> () in
                                })
                                reactions.append(failureReaction)
                                failure(context)
                            }');

            }
        }
    }
}

if( !isset($successMicroBlock)){

    if($successTypeString==''){
        // there is no return type
        $successMicroBlock = 'success()';
    }else{
        $successMicroBlock ='

if let r=result.value as? ' . $successTypeString . '{
    success(r)
 }else{
    let failureReaction =  Reaction.dispatchAdaptiveMessage(
        context: context,
        title: NSLocalizedString("Deserialization issue",
            comment: "Deserialization issue"),
        body:"\(result.value)",
        transmit:{ (selectedIndex) -> () in
    })
   reactions.append(failureReaction)
   failure(context)
}';
    }


}

$parameterEncodingString='JSON';
if($d->httpMethod=='GET'){
    $parameterEncodingString='URL';
}
echo('
        if let document = Bartleby.sharedInstance.getDocumentByUID(documentUID) {
            let pathURL=document.baseURL.appendingPathComponent("'.$path.'")
            '.(($d->containsParametersOutOfPath()?'let dictionary:[String:Any]? = parameters.dictionaryRepresentation()':'let dictionary:[String:Any]=[String:Any]()')).'
            let urlRequest=HTTPManager.requestWithToken(inDocumentWithUID:document.UID,withActionName:"'.$d->class.'" ,forMethod:"'.$d->httpMethod.'", and: pathURL)
            
            do {
                let r=try '.($parameterEncodingString=='JSON' ? 'JSONEncoding()' : 'URLEncoding()').'.encode(urlRequest,with:dictionary)
                request(r).responseData(completionHandler: { (response) in
                  
                    let request=response.request
                    let result=response.result
                    let timeline=response.timeline
                    let statusCode=response.response?.statusCode ?? 0
                    
                    let context = HTTPContext( code: '.crc32($d->class).',
                        caller: "'.$d->class.'.execute",
                        relatedURL:request?.url,
                        httpStatusCode: statusCode)
                        
                    if let request=request{
                        context.request=HTTPRequest(urlRequest: request)
                    }

                    if let data = response.data, let utf8Text = String(data: data, encoding: .utf8) {
                        context.responseString=utf8Text
                    }
'.$metricsBlock.'
                    // React according to the situation
                    var reactions = Array<Reaction> ()
            
                    if result.isFailure {
                       let failureReaction =  Reaction.dispatchAdaptiveMessage(
                            context: context,
                            title: NSLocalizedString("Unsuccessfull attempt",comment: "Unsuccessfull attempt"),
                            body:"\(String(describing: result.value))\n\(#file)\n\(#function)\nhttp Status code: (\(statusCode))",
                            transmit:{ (selectedIndex) -> () in
                        })
                        reactions.append(failureReaction)
                        failure(context)
            
                    }else{
                          if 200...299 ~= statusCode {
'.
$successMicroBlock
.
'                         }else{
                            // Bartlby does not currenlty discriminate status codes 100 & 101
                            // and treats any status code >= 300 the same way
                            // because we consider that failures differentiations could be done by the caller.
                            let failureReaction =  Reaction.dispatchAdaptiveMessage(
                                context: context,
                                title: NSLocalizedString("Unsuccessfull attempt",comment: "Unsuccessfull attempt"),
                                body:"\(String(describing: result.value))\n\(#file)\n\(#function)\nhttp Status code: (\(statusCode))",
                                transmit:{ (selectedIndex) -> () in
                            })
                           reactions.append(failureReaction)
                           failure(context)
                        }
                        
                 }
                 //Let s react according to the context.
                 document.perform(reactions, forContext: context)
            })
        }catch{
                let context = HTTPContext( code:2 ,
                caller: "'.$actionClassName.'.execute",
                relatedURL:nil,
                httpStatusCode:500)
                failure(context)
        }
      }else{
         let context = HTTPContext( code: 1,
                caller: "'.$actionClassName.'.execute",
                relatedURL:nil,
                httpStatusCode: 417)
         failure(context)
       }
    }
}
');
?>