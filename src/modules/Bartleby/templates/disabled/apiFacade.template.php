<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ProjectRepresentation */


if (isset ( $f )) {
    $f->fileName = 'ApiFacade.class.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString();
}/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>