<?php
/* @var $f Flexed */
/* @var $d EntityRepresentation */

require_once FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';
if (isset ( $f )) {
    $f->fileName = ucfirst($d->name).'.class.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'models/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

class <?php echo ucfirst($d->name)?>{
<?php
/* @var $property PropertyRepresentation */

// You can distinguish the first, and last property
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name=$property->name;
    if($d->firstProperty()){
        echoIndent('var $'.$name.';'.cr(),1);
    }else if ($d->lastProperty()){
        echoIndent('var $'.$name.';',1);
    }else{
        echoIndent('var $'.$name.';'.cr(),1);
    }
}
?>
}
<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>