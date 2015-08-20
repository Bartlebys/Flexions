<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */


if (isset ( $f )) {
    $f->fileName = 'ok.txt';
    $f->package = 'php/';
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d).cr(); ?>
OK <?php echo cr().'Version:'.$h->version.cr().'Stage:'.ucfirst($h->stage);?>
<?php /*<- END OF TEMPLATE */?>