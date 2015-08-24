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
namespace Bartleby\Models;

require_once BARTLEBY_ROOT_FOLDER.'/core/Model.php';
use Bartleby\Core\Model;


<?php while ($d->iterateOnProperties()){
    $property=$d->getProperty();
    if($property->isGeneratedType){
        $className=$property->instanceOf;
        $className=$h->ucFirstRemovePrefixFromString($className);
        echoIndentCR('require_once dirname(__DIR__).\'/models/'.$className.'.php\';',0);
        echoIndentCR('use Bartleby\Models\\'.$className.';',0);
    }
} ?>


class <?php echo $classNameWithoutPrefix?> extends Model{
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

    function classMapping(array $mapping=array()){
<?php while ($d->iterateOnProperties()){
    $property=$d->getProperty();
    $typeOfProp=$property->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    if (($typeOfProp===$o)||($typeOfProp===$c)){
        $type=$property->instanceOf;
        if($property->isGeneratedType) {
            $type = $h->ucFirstRemovePrefixFromString($type);
        }
    }

    if ($property->type==FlexionsTypes::COLLECTION){
        echoIndentCR( '$mapping[\''.$property->name.'\']=array(\''.$type.'\');',2);
    }else{
        echoIndentCR( '$mapping[\''.$property->name.'\']=\''.$type.'\';',2);
    }


}?>
    return parent::classMapping($mapping);
    }

}

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>
