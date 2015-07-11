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
foreach ($d->properties as $property ) {
    echoIndent('var $'.$property->name.';'.cr(),1);
}
echoIndent(cr().'}',0);
echoIndent(cr().'?>',0);


