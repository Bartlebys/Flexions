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
    $f->fileName = 'ok.txt';
    $f->package = 'php/';
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d).cr(); ?>
OK <?php echo cr().'Version:'.$h->version.cr().'Stage:'.ucfirst($h->stage);?>
<?php /*<- END OF TEMPLATE */?>