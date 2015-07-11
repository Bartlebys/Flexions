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

// If you add a path to the preserve path it will be generated once only
// If the file does not exists.
$h->preservePath[]='v1/Api.class.php';
$h->preservePath[]='v1/Config.php';
$h->preservePath[]='v1/Const.php';

/// Associate the entities to the loop name
if(! $h->setLoopDescriptor($r->entities,DefaultLoops::ENTITIES)){
    throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ENTITIES);
}

/// Associate the global descriptor to the loop name
// Yoy must wrap it in an array
if(! $h->setLoopDescriptor($r->actions,DefaultLoops::ACTIONS)){
    throw new Exception('Error when setting the loop descriptor '.DefaultLoops::ACTIONS);
}

/// Associate the global descriptor to the loop name
// Yoy must wrap it in an array
if(! $h->setLoopDescriptor(array($r),DefaultLoops::PROJECT)){
    throw new Exception('Error when setting the loop descriptor '.DefaultLoops::PROJECT);
}