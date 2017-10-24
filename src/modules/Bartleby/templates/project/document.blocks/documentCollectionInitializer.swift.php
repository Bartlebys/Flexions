
<?php

//////////////////////
// EXCLUSIONS
//////////////////////

if (!isset($project)){
    return '$project required in '.__FILE__;
}


//////////////////////////
// VARIABLES DEFINITIONS
//////////////////////////

$collectionInitializationBlock='';
foreach ($project->entities as $entity) {
    if ($configurator->managedCollectionShouldBeSupportedForEntity($project,$entity) && !$entity->isUnManagedModel()){
        $pluralizedEntity=Pluralization::pluralize($entity->name);
        $collectionControllerClassName = 'Managed'.$pluralizedEntity;
        $collectionInitializationBlock .= stringIndent('@objc open dynamic var '.lcfirst($pluralizedEntity).'='.$collectionControllerClassName.'()'.cr(),1);
    }
}

//////////////////////////
// BLOCK
//////////////////////////

?>

