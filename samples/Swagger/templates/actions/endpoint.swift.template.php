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

class <?php echo $d->class.'Parameters';?>{
}

class <?php echo $d->class; ?>{

    static let HTTP_METHOD="<?php echo($d->httpMethod)?>"

    <?php
    // We want to inject the path variable into the
    $pathVariables=GenerativeHelper::variablesFromPath($d->path);
    if(count($pathVariables)>0){
        foreach ($pathVariables as $pathVariable ) {
            echoIndent('static var '.$pathVariable.'=""'.cr(),0);
        }
    }
    ?>

    static let path="<?php
// We want to inject the path variable into the
$pathVariables=GenerativeHelper::variablesFromPath($d->path);
$path=$d->path;
if(count($pathVariables)>0){
}
echo($path);
?>"

    static func execute(parameters:<?php echo $d->class.'Parameters';?>,
                        sucessHandler success:(object:Class_to_be_defined)->(),
                        failureHandler failure:(result:Class_to_be_defined)->()){

    }
}

<?php /*<- END OF TEMPLATE */ ?>