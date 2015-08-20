<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';


/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    $classNameWithoutPrefix=ucfirst(substr($d->name,strlen($h->classPrefix)));
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'generated/models/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

class <?php echo $classNameWithoutPrefix?>{
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
