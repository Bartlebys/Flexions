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
    echoIndent('class ' . $d->class . 'Parameters{'.cr(), 0);
    while ($d->iterateOnParameters() === true) {
        $property = $d->getParameter();
        $name = $property->name;
        if(!$d->parameterIsInPath($name)){
            echoIndent('// ' .$property->description. cr(), 1);
            if ($d->firstParameter()) {
            }
            if($property->type==FlexionsTypes::ENUM) {
                $enumTypeName = $d->name . ucfirst($name);
                echoIndent('enum ' . $enumTypeName . ' : ' . ucfirst($property->instanceOf) . '{' . cr(), 1);
                foreach ($property->enumerations as $element) {
                    if ($property->instanceOf == FlexionsTypes::STRING) {
                        echoIndent('case ' . ucfirst($element) . ' = "' . $element . '"' . cr(), 2);
                    } else {
                        echoIndent('case ' . ucfirst($element) . ' = ' . $element . '' . cr(), 2);
                    }
                }
                echoIndent('}' . cr(), 1);
                echoIndent('var ' . $name . ':' . $enumTypeName . '?' . cr(), 1);
            }else if ($property->type == FlexionsTypes::COLLECTION) {
                echoIndent('var ' . $name . ':[' . ucfirst($property->instanceOf) . ']?' . cr(), 1);
            } else if ($property->type == FlexionsTypes::OBJECT) {
                echoIndent('var ' . $name . ':' . ucfirst($property->instanceOf) . '?' . cr(), 1);
            } else {
                $nativeType = FlexionsSwiftLang::nativeTypeFor($property->type);
                if (strpos($nativeType, FlexionsTypes::NOT_SUPPORTED) === false) {
                    echoIndent('var ' . $name . ':' . $nativeType . '?' . cr(), 1);
                } else {
                    echoIndent('var ' . $name . ':Not_Supported = Not_Supported//' . ucfirst($property->type) . cr(), 1);
                }
            }
            if ($d->lastParameter()) {
            }
        }

    }
    echoIndent('}'.cr(), 0);
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
echoIndent('failureHandler failure:(result:[String:Any])->()){'.cr(), 6);
// We want to inject the path variable into the
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path=$d->path;
if(count($pathVariables)>0){
    foreach ($pathVariables as $pathVariable ) {
        $path=str_ireplace('{'.$pathVariable.'}','\('.$pathVariable.')',$path);
    }
}
echoIndent('if  let pathURL=Configuration.baseUrl?.URLByAppendingPathComponent("'.$path.'") {'.cr(),7);
if($d->httpMethod=="GET"){
    echoIndent('request(.GET, pathURL, parameters: ["foo": "bar"])'.cr(),7);
    echoIndent('.validate(statusCode: 200..<300)'.cr(),8);
    echoIndent('.validate(contentType: ["application/json"])'.cr(),8);
        echoIndent('.response { _, _, _, error in'.cr(),8);
            echoIndent('println(error)'.cr(),9);
        echoIndent('}'.cr(),8);
}
if($d->httpMethod=="POST"){

}
if($d->httpMethod=="PUT"){

}
if($d->httpMethod=="DELETE"){

}
echoIndent("} else { ".cr(),7);
echoIndent('failure(result: ["Error" :"invalid pathURL for path:'.$path.'"])'.cr(),8);
echoIndent("}".cr(),7);
?>


    }
}

<?php /*<- END OF TEMPLATE */ ?>