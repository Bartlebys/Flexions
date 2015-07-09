<?php

/* @var $f Flexed */

$prefix = "MusicPlayer";

if (isset ( $f )) {
	$f->package = "Models/";
	$f->company = "Chaosmos";
	$f->prefix = $prefix;
	$f->author = "benoit@pereira-da-silva.com";
	$f->projectName = "MPS-Sample";
	$f->license = FLEXIONS_ROOT_DIR."flexions/helpers/licenses/LGPL.tpl.php";
}
$parentClass = "ChaosmosBaseModel";
$collectionParentClass="ChaosmosCollectionOfModel";
$protocols="ChaosmosCoding";
$imports = "\n#import \"$parentClass.h\"\n"; // NOT NEEDED FOR SWIFT
$markAsDynamic = false;
$allowScalars = true;
