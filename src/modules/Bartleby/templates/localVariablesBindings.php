<?php

// Flexions 3.0 bindings
// Injects the variables to the flexions 2.0 templates the local scope.
// And sets default value if not set.

$h = Hypotypose::Instance();
$registry = Registry::Instance();

$modelsShouldConformToCodable = $registry->valueForKey('modelsShouldConformToCodable');
$modelsShouldConformToNSSecureCoding = $registry->valueForKey('modelsShouldConformToNSSecureCoding');
$modelsShouldConformToMappable = $registry->valueForKey('modelsShouldConformToMappable');
$modelsShouldConformToExposed = $registry->valueForKey('modelsShouldConformToExposed');
$hideImportDirectives = $registry->valueForKey('hideImportDirectives');
$outSideBartleby = $registry->valueForKey('outSideBartleby');
$excludeEntitiesWith = $registry->valueForKey('excludeEntitiesWith');
$xOSIncludeManagedCollectionForEntityNamed = $registry->valueForKey('xOSIncludeManagedCollectionForEntityNamed');
$doNotGenerate = $registry->valueForKey('doNotGenerate');
$excludeActionsWith = $registry->valueForKey('excludeActionsWith');
$excludeFromServerActionsWith = $registry->valueForKey('excludeFromServerActionsWith');
$unDeletableEntitiesWith = $registry->valueForKey('unDeletableEntitiesWith');
$unModifiableEntitiesWith = $registry->valueForKey('unModifiableEntitiesWith');
$doNotGenerate = $registry->valueForKey('doNotGenerate');
$isIncludeInBartlebysCommons = $registry->valueForKey('isIncludeInBartlebysCommons');
$isBartlebysCore = $registry->valueForKey('isBartlebysCore');
$configurator = $registry->valueForKey('configurator');

if(!isset($doNotGenerate)){
    $doNotGenerate = array();
}

if (!isset($outSideBartleby)){
    $outSideBartleby = false;
}
if (!isset($hideImportDirectives)){
    $hideImportDirectives = false;
}
if (!isset($modelsShouldConformToCodable)) {
    $modelsShouldConformToCodable = false;
}
if (!isset($modelsShouldConformToNSSecureCoding)) {
    $modelsShouldConformToNSSecureCoding = false;
}
if (!isset($modelsShouldConformToMappable)) {
    $modelsShouldConformToMappable = false;
}
if (!isset($modelsShouldConformToExposed)) {
    $modelsShouldConformToExposed = false;
}
if (!isset($isIncludeInBartlebysCommons)) {
    $isIncludeInBartlebysCommons = false;
}