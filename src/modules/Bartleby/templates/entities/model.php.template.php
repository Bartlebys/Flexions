<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';


/* @var $f Flexed */
/* @var $d EntityRepresentation */

if (isset ( $f )) {
    $classNameWithoutPrefix=ucfirst(substr($d->name,strlen($h->classPrefix)));
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'generated/models/';
}


// Exclusion

$exclusion = array();
$exclusionName = str_replace($h->classPrefix, '', $d->name);

if (isset($excludeEntitiesWith)) {
    $exclusion = $excludeEntitiesWith;
}
foreach ($exclusion as $exclusionString) {
    if (strpos($exclusionName, $exclusionString) !== false) {
        return NULL; // We return null
    }
}



/* TEMPLATES STARTS HERE -> */?><?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>
namespace Bartleby\Models;

require_once BARTLEBY_ROOT_FOLDER.'core/Model.php';
use Bartleby\Core\Model;
<?php
$hasBeenImported=array();
while ($d->iterateOnProperties()){
    $property=$d->getProperty();
    if($property->isGeneratedType){
        $className=$property->instanceOf;
        $className=$h->ucFirstRemovePrefixFromString($className);
        if (! in_array($className,$hasBeenImported)) {
            echoIndentCR('require_once dirname(__DIR__).\'/models/'.$className.'.php\';',0);
            echoIndentCR('use Bartleby\Models\\'.$className.';',0);
            $hasBeenImported[]=$className;
        }

    }
} ?>

class <?php echo $classNameWithoutPrefix?> extends Model{

<?php
/* @var $property PropertyRepresentation */

// You can distinguish the first, and last property
while ( $d ->iterateOnProperties() === true ) {
    $property = $d->getProperty();
    $name=$property->name;
    $typeOfProp=$property->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    if (($typeOfProp===$o)||($typeOfProp===$c)) {
        $typeOfProp = $h->ucFirstRemovePrefixFromString($property->instanceOf);
        if($typeOfProp==$c){
            $typeOfProp=' array of '.$typeOfProp;
        }
    }


    if($property->type==FlexionsTypes::ENUM){
        $enumTypeName=ucfirst($name);
        $typeOfProp=$property->instanceOf.' '.$typeOfProp;
        echoIndentCR('// Enumeration of possibles values of '.$name, 1);
        foreach ($property->enumerations as $element) {
            if($property->instanceOf==FlexionsTypes::STRING){
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = "'.$element.'";' ,1);
            }else{
                echoIndentCR('const ' .$enumTypeName.'_'.ucfirst($element).' = '.$element.';', 1);
            }
        }
    }
    echoIndentCR('/* @var '.$typeOfProp.' '.$property->description.' */',1);
    if($d->firstProperty()){
        echoIndentCR('public $'.$name.';',1);
    }else if ($d->lastProperty()){
        echoIndent('public $'.$name.';',1);
    }else{
        echoIndentCR('public $'.$name.';',1);
    };
    echoIndentCR('',0);



    if($d->lastProperty()){
        echoIndent(cr(),0);
    }



}
?>


    function classMapping(array $mapping=array()){
<?php while ($d->iterateOnProperties()){
    $property=$d->getProperty();
    $typeOfProp=$property->type;
    $o=FlexionsTypes::OBJECT;
    $c=FlexionsTypes::COLLECTION;
    if (($typeOfProp===$o)||($typeOfProp===$c)) {
        $type = $property->instanceOf;
        if ($property->isGeneratedType) {
            $type = $h->ucFirstRemovePrefixFromString($type);
        }
        if ($property->type == FlexionsTypes::COLLECTION) {
            echoIndentCR('$mapping[\'' . $property->name . '\']=array(\'' . $type . '\');', 2);
        } else {
            echoIndentCR('$mapping[\'' . $property->name . '\']=\'' . $type . '\';', 2);
        }
    }


}?>
        return parent::classMapping($mapping);
    }

}

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>
