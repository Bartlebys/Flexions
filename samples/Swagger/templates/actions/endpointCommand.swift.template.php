<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

require_once FLEXIONS_SOURCE_DIR.'/SharedMPS.php';

/* @var $d ActionRepresentation*/
$d;

if (isset ( $f )) {
    $f->fileName = $d->class.'.swift';
    $f->package = "iOS/swift/commands/";
}

echo('/**/');