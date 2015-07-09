<?php

/* @var $f Flexed */

$prefix = "SLY";

if (isset ( $f )) {
	$f->package = "Models/";
	$f->company = "SLY";
	$f->prefix = $prefix;
	$f->author = "benoit@pereira-da-silva.com";
	$f->projectName = "SLYPlayer";
	//$f->license = FLEXIONS_ROOT_DIR."flexions/helpers/licenses/LGPL.tpl.php";
}
$parentClass = "WattModel";
$collectionParentClass="WattCollectionOfModel";
//$protocols="WattCoding,WattCopying,WattExtraction";
$imports = "\n#import \"$parentClass.h\"\n";
$markAsDynamic = false;
$allowScalars = true;
