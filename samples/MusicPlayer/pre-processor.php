<?php

require_once FLEXIONS_ROOT_DIR . 'flexions/representations/flexions/FlexionsRepresentationsIncludes.php';
require_once FLEXIONS_MODULES_DIR.'XcDataModelXMLImporter/XcdatamodelXMLToFlexionsRepresentation.class.php';
require_once FLEXIONS_MODULES_DIR . 'XcDataModelXMLImporter/XcdataModelDelegate.class.php';

/* @var $descriptorFilePath string */
include  FLEXIONS_SOURCE_DIR.'/SharedMusicPlayer.php';// we load the shared variables
/* @var $prefix string */

$transformer=new XCDDataXMLToFlexionsRepresentation();
$delegate=new XcdataModelDelegate();
$r=$transformer->projectRepresentationFromXcodeModel($descriptorFilePath,$prefix,$delegate);

// we instanciate the Hypotypose singleton;
$h = Hypotypose::instance ();
$h->classPrefix=$r->classPrefix;


/// Associate the entities to the loop name
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
	throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}