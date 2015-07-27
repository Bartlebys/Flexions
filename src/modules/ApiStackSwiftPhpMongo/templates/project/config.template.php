<?php

require_once FLEXIONS_MODULES_DIR . '/ApiStackSwiftPhpMongo/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */


if (isset ( $f )) {
    $f->fileName = 'Config.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString();

}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

/* @var $action ActionRepresentation */
foreach ($d->actions as $action ) {
    echo('require_once "/endpoints/'.$action->class.'.class.php";'.cr());
}

define('DB_NAME',("<? echo ucfirst($d->name);?>"));
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>