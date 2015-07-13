<?php

/* @var $f Flexed */

require_once FLEXIONS_MODULES_DIR .'Utils/Pluralization.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/GenerativeHelperForPhp.class.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/GenerativeHelperForSwift.class.php';

$prefix = "Swagger";

if (isset ( $f )) {
	$f->package = "Models/";
	$f->company = "Chaosmos";
	$f->prefix = $prefix;
	$f->author = "benoit@pereira-da-silva.com";
	$f->projectName = "Swagger-Sample";
	$f->license = FLEXIONS_MODULES_DIR."licenses/LGPL.template.php";
}

$parentClass = "ChaosmosBaseModel";
$collectionParentClass="ChaosmosCollectionOfModel";
$protocols="ChaosmosCoding";
$imports = "\n#import \"$parentClass.h\"\n"; // NOT NEEDED FOR SWIFT
$markAsDynamic = false;
$allowScalars = true;