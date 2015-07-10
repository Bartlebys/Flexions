<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/helpers/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_ROOT_DIR.'modules/XcDataModelXMLImporter/XcdatamodelXMLToFlexionsRepresentation.class.php';

/* @var $descriptorFilePath string */
include  FLEXIONS_SOURCE_DIR.'/variables-for-MusicPlayer.php';// we load the shared variables
/* @var $prefix string */

$transformer=new XCDDataXMLToFlexionsRepresentation();
$r=$transformer->projectRepresentationFromXcodeModel($descriptorFilePath,$prefix);

// we instanciate the Hypotypose singleton;
$h = Hypotypose::instance ();
$h->classPrefix=$r->classPrefix;


/// Associate the entities to the loop name
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
	throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}