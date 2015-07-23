<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

/* @var $f Flexed */
/* @var $d ProjectRepresentation */

require_once FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';

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