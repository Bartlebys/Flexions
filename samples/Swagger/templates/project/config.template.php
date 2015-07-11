<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

/* @var $d ProjectRepresentation */
$d;

require_once FLEXIONS_SOURCE_DIR.'/SharedMPS.php';

if (isset ( $f )) {
    $f->fileName = 'Config.php';
    $f->package = "php/api/v1/";
}
echo('<?php'.cr());
/* @var $action ActionRepresentation */
foreach ($d->actions as $action ) {
    echo('require_once "/endpoints/'.$action->class.'.class.php";'.cr());
}
echo(cr().'?>');



