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
    $f->fileName = 'ApiFacade.class.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString();
}/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>


