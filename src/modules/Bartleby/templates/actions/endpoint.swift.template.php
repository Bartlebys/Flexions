<?php
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation */

if (isset ($f)) {
    $f->fileName = $d->class . '.swift';
    $f->package = 'xOS/operations/';
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

        if (!$d->parameterIsInPath($name)){
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
        if (!$d->parameterIsInPath($name)){
            echoIndentCR($name . ' <- map["' . $name . '"]', 2);
        }
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
$hasdID= in_array('dID',$pathVariables);
if (!$hasdID){
    echoIndentCR('dID:String,',$pathVCounter>0);
}

if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        if ($pathVariable=='dID'){
            $hasdID=true;
        }
        // Suspended
        echoIndentCR($pathVariable.':String,',6);
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
    $successParameterName= Pluralization::pluralize(lcfirst($h->ucFirstRemovePrefixFromString($successP->instanceOf)));
}else{
    if($successP->isGeneratedType==true){
        $successParameterName=lcfirst($h->ucFirstRemovePrefixFromString($successTypeString));
    }else{
        $successParameterName='result';
    }
}


$resultSuccessTypeString=$successTypeString!=''?$successParameterName.':'.$successTypeString:'';
if ($d->containsParametersOutOfPath()) {
    echoIndentCR('parameters:' . $d->class . 'Parameters,' , 6);
    echoIndentCR('sucessHandler success:(' . $resultSuccessTypeString . ')->(),', 6);
} else {
    echoIndentCR('sucessHandler success:(' . $resultSuccessTypeString . ')->(),', 6);
}

// We want to inject the path variable
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path=$d->path;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        $path=str_ireplace('{'.$pathVariable.'}','\('.$pathVariable.')',$path);
    }
}

echoIndentCR('failureHandler failure:(context:JHTTPResponse)->()){', 6);
echoIndentCR('');
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
   let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
        context: context,
        title: NSLocalizedString("Deserialization issue",
            comment: "Deserialization issue"),
        body:"(result.value)",
        trigger:{ (selectedIndex) -> () in
            print("Post presentation message selectedIndex:\(selectedIndex)")
    })
   reactions.append(failureReaction)
   failure(context:context)
}',5);
            }
        }
    }
}

if( !isset($successMicroBlock)){

    if($successTypeString==''){
        // there is no return type
        $successMicroBlock =stringIndentCR('success()',4);
    }else{
        $successMicroBlock =stringIndent(
            '
if let r=result.value as? ' . $successTypeString . '{

    success(' . $successParameterName . ':r)
 }else{
    let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
        context: context,
        title: NSLocalizedString("Deserialization issue",
            comment: "Deserialization issue"),
        body:"(result.value)",
        trigger:{ (selectedIndex) -> () in
            print("Post presentation message selectedIndex:\(selectedIndex)")
    })
   reactions.append(failureReaction)
   failure(context:context)
}',2);
    }


}

$parameterEncodingString='JSON';
if($d->httpMethod=='GET'){
    $parameterEncodingString='URL';
}
    echoIndentCR(
'
    let pathURL=Configuration.BASE_URL.URLByAppendingPathComponent("'.$path.'")
    '.(($d->containsParametersOutOfPath()?'let dictionary:Dictionary<String, AnyObject>?=Mapper().toJSON(parameters)':'let dictionary:Dictionary<String, AnyObject>=[:]')).'
    let urlRequest=HTTPManager.mutableRequestWithToken(documentID:dID,withActionName:"'.$d->class.'" ,forMethod:Method.'.$d->httpMethod.', and: pathURL)
    let r:Request=request(ParameterEncoding.'.$parameterEncodingString.'.encode(urlRequest, parameters: dictionary).0)
    r.'.(($successTypeString=='')?'responseString':'responseJSON').'{ response in

	    let request=response.request
        let result=response.result
        let response=response.response


        // Bartleby consignation

        let context = JHTTPResponse( code: '.crc32($d->class).',
            caller: "'.$d->class.'.execute",
            relatedURL:request?.URL,
            httpStatusCode: response?.statusCode ?? 0,
            response: response )

        // React according to the situation
        var reactions = Array<Bartleby.Reaction> ()
        reactions.append(Bartleby.Reaction.Track(result: nil, context: context)) // Tracking

        if result.isFailure {
           let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                context: context,
                title: NSLocalizedString("Unsuccessfull attempt",comment: "Unsuccessfull attempt"),
                body:NSLocalizedString("Explicit Failure",comment: "Explicit Failure"),
                trigger:{ (selectedIndex) -> () in
                    print("Post presentation message selectedIndex:\(selectedIndex)")
            })
            reactions.append(failureReaction)
            failure(context:context)

        }else{
            if let statusCode=response?.statusCode {
                if 200...299 ~= statusCode {
'.$successMicroBlock.'
            }else{
                // Bartlby does not currenlty discriminate status codes 100 & 101
                // and treats any status code >= 300 the same way
                // because we consider that failures differentiations could be done by the caller.
                let failureReaction =  Bartleby.Reaction.DispatchAdaptiveMessage(
                    context: context,
                    title: NSLocalizedString("Unsuccessfull attempt",comment: "Unsuccessfull attempt"),
                    body:NSLocalizedString("Implicit Failure",comment: "Implicit Failure"),
                    trigger:{ (selectedIndex) -> () in
                        print("Post presentation message selectedIndex:\(selectedIndex)")
                })
               reactions.append(failureReaction)
               failure(context:context)
            }
        }
     }

     //Let s react according to the context.
     Bartleby.sharedInstance.perform(reactions, forContext: context)

  }
}
',4);

echoIndentCR('}',0)
?><?php /*<- END OF TEMPLATE */ ?>
