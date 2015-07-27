<?php

require_once FLEXIONS_MODULES_DIR . '/ApiStackSwiftPhpMongo/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation*/

if (isset ( $f )) {
    $f->fileName = $d->class.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'endpoints/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

// Path : <?php echo $d->path.cr(); ?>
class <?php echo $d->class; ?>{
    const HTTP_METHOD="<?php echo($d->httpMethod)?>";
    const PATH="<?php echo($d->path)?>";
}
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>

