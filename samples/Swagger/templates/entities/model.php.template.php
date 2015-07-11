<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

/* @var $d EntityRepresentation */

require_once FLEXIONS_SOURCE_DIR.'/SharedMPS.php';

if (isset ( $f )) {
    $f->fileName = ucfirst($d->name).'.class.php';
    $f->package = "php/api/v1/models/";
}
echoIndent('<?php'.cr(),0);
echoIndent('class '.ucfirst($d->name).'{'.cr(),0);
/* @var $property PropertyRepresentation */

// You can distinguish the first, and last property
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name=$property->name;

    if($d->firstProperty()){
        echoIndent('var $'.$name.';'.cr(),1);
    }else if ($d->lastProperty()){
        echoIndent('var $'.$name.';',1);
    }else{
        echoIndent('var $'.$name.';'.cr(),1);
    }
}

echoIndent(cr().'}',0);
echoIndent(cr().'?>',0);


