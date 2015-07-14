<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

require_once FLEXIONS_SOURCE_DIR . '/SharedSwagger.php';


/* @var $f Flexed */
/* @var $d ActionRepresentation */

if (isset ($f)) {
    $f->fileName = $d->class . '.swift';
    $f->package = 'iOS/swift/endpoints/';
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo GenerativeHelperForSwift::defaultHeader($f,$d); ?>
// Path : <?php echo $d->path.cr(); ?>
@class <?php echo $d->class; ?>{
}
<?php echo '?>' ?>
<?php /*<- END OF TEMPLATE */ ?>