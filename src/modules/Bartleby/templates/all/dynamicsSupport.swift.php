<?php
include  FLEXIONS_MODULES_DIR . '/Bartleby/templates/localVariablesBindings.php';
require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';
require_once FLEXIONS_MODULES_DIR . '/Languages/FlexionsSwiftLang.php';
require_once FLEXIONS_ROOT_DIR . '/flexions/core/Hypotypose.php';

/* @var $f Flexed */
/* @var $d mixed*/

$typeName ='NO_NAME';
if (isset ( $f )) {
    // We determine the file name.
    if ($isIncludeInBartlebysCommons){
        $typeName= 'BartlebysDynamics';
    }else{
        $typeName = 'AppDynamics';
    }
    $f->fileName = $typeName.'.swift';
    // And its package.
    $f->package = 'xOS/';
}

$names = [];


if ($d instanceof EntityRepresentation){
    /* @var $entityRepresentation EntityRepresentation */
    $entityRepresentation = $d;

    // Determine if we should allow dynamic instanciation of the collection.
    $forced = false;
    if (isset($xOSIncludeManagedCollectionForEntityNamed)){
        foreach ($xOSIncludeManagedCollectionForEntityNamed as $inclusion) {
            if (strpos($entityRepresentation->name, $inclusion) !== false) {
                $forced = true;
            }
        }
    }
    $managedCollectionName = 'Managed'.ucfirst(Pluralization::pluralize($entityRepresentation->name));
    $names []= $entityRepresentation->name;
    if ($forced || ( $entityRepresentation->isDistantPersistencyOfCollectionAllowed()
        && !$entityRepresentation->isUnManagedModel()
        && !$entityRepresentation->isBaseObject())
    && !in_array($managedCollectionName,$doNotGenerate)){
        $names []= $managedCollectionName;
    }
}elseif ($d instanceof ProjectRepresentation){
    // We have nothing to generate
}elseif ($d instanceof ActionRepresentation){
    /* @var $actionRepresentation ActionRepresentation */
    $actionRepresentation = $d;
    $names []= $actionRepresentation->class;
}


//////////////////
// EXCLUSIONS
//////////////////

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

/////////////////////////
// VARIABLES COMPUTATION
/////////////////////////

$importDirectives = '';
$overrideString = '';
if($isIncludeInBartlebysCommons){
    $importDirectives = 'import Foundation'.cr();
}else {
    $importDirectives = 'import Foundation
#if !USE_EMBEDDED_MODULES
    import BartlebyKit
#endif 
';
    $overrideString = 'override ';
}


$superDeserializeInvocation='';
$superInitInvocation=cr();
$superNewInstanceInvocation='';
if($isIncludeInBartlebysCommons) {
    $superDeserializeInvocation = 'throw DynamicsError.typeNotFound'.cr();
    $superNewInstanceInvocation = 'throw DynamicsError.typeNotFound'.cr();
}else{
    $superInitInvocation = 'super.init()'.cr();
    $superDeserializeInvocation = 'return try super.deserialize(typeName:typeName,data:data,document:document)'.cr();
    $superNewInstanceInvocation ='return try super.newInstanceOf(typeName)'.cr();
}





//////////////////
// TEMPLATE
//////////////////

$flexedList = Hypotypose::Instance()->flexedList;
$source = NULL;
foreach ($flexedList as $k=>$flexed) {
    if ($f->uniqueIdentifier() == $k){
        /* @var $foundFlexed Flexed */
        $foundFlexed= $flexedList[$k];
        $source = $foundFlexed->source;
        break;
    }
}

// This template is complex.
// We create a source file.
// Then inject

if (is_null($source)){

    //////////////////////////////////////////
    // It is the first Iteration on this file
    // Start of Initial source generationb
    //////////////////////////////////////////

    // Collect the block
    ob_start();
    @include __DIR__ . '/dynamicsSupport.block.php';
    $source = ob_get_clean();

}

foreach ($names as $name) {
    // We don't need to perform on ProjectRepresentation
    if ($name != 'ProjectRepresentation'){
        // We use injection tags to determinate where to put our iterative code.

        $injectionTag = '//FLEXIONS_TAG_001';
        $tagPos = strpos($source,$injectionTag)+strlen($injectionTag);
        $start = substr($source,0, $tagPos);
        $end = substr($source,$tagPos);
        $start .= cr()."        if typeName == \"$name\"{ instance = try JSON.decoder.decode($name.self, from: data); return instance }";
        $source = $start . $end;


        $injectionTag = '//FLEXIONS_TAG_002';
        $tagPos = strpos($source,$injectionTag)+strlen($injectionTag);
        $start = substr($source,0, $tagPos);
        $end = substr($source,$tagPos);
        $start .= cr()."        if typeName == \"$name\"{ return $name() }";
        $source = $start . $end;
    }
}

echo $source;