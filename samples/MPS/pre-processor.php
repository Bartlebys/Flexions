<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/helpers/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_ROOT_DIR.'modules/XcDataModelXMLImporter/XcdatamodelXMLToFlexionsRepresentation.class.php';

/* @var $descriptorFilePath string */
include  FLEXIONS_SOURCE_DIR.'/variables-for-swift.php';// we load the shared variables
/* @var $prefix string */

$transformer=new XCDDataXMLToFlexionsRepresentation();
$r=$transformer->projectRepresentationFromXcodeModel($descriptorFilePath,$prefix);

// we instanciate the Hypotypose singleton;
$h = Hypotypose::instance (array(DefaultLoops::ENTITIES));
$h->classPrefix=$r->classPrefix;

// We store the entities the Hypotypose.
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
	throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}