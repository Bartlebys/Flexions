<?php


// we load the shared variables
include  FLEXIONS_SOURCE_DIR.'/SharedMPS.php';
require_once FLEXIONS_MODULES_DIR . 'SwaggerToFlexions/SwaggerToFlexionsRepresentations.class.php';
require_once FLEXIONS_MODULES_DIR . 'SwaggerToFlexions/SwaggerDelegate.class.php';

$transformer=new SwaggerToFlexionsRepresentations();
$delegate=new SWaggerDelegate();
$r=$transformer->projectRepresentationFromSwaggerJson($descriptorFilePath,$prefix,$delegate);

// we instanciate the Hypotypose singleton
$h = Hypotypose::instance();
$h->classPrefix=$r->classPrefix;

/*

$p=array(
    DefaultLoops::ENTITIES=>$r->entities//,
    DefaultLoops::ACTIONS=>$r->actions,
    DefaultLoops::PROJECT=>$r->actions
);

/// Associate the project data to be  the loop name
if(! $h->setLoopDescriptor($p ,'Custom descriptor')){
	throw new Exception('Error when setting the custom loop descriptor ');
}
*/


/// Associate the entities to the loop name
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
    throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}