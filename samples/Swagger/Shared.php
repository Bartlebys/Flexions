<?php

/* @var $f Flexed */

require_once FLEXIONS_MODULES_DIR . 'Utils/Pluralization.php';


$prefix = "Swagger";

if (isset ( $f )) {
	$f->package = "Models/";
	$f->company = "Chaosmos";
	$f->prefix = $prefix;
	$f->author = "benoit@chaosmos.fr";
	$f->projectName = "Swagger-Sample";
	$f->license = FLEXIONS_MODULES_DIR."Licenses/LGPL.template.php";
}

/*
$parentClass = "";
$collectionParentClass="";
$protocols="";
$imports = "\n#import \"$parentClass.h\"\n"; // NOT NEEDED FOR SWIFT
$markAsDynamic = false;
$allowScalars = true;
*/