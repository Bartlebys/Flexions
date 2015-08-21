<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */


if (isset ( $f )) {
    $f->fileName = 'destructiveInstaller.php';
    $f->package = "php/";
}
/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>

/**
*
* This script should be destroyed and not deployed.
* A destructive installer script for YouDub
*
**/

namespace Bartleby;
use \MongoClient;
require_once __DIR__.'/Configuration.php';

$configuration=new Configuration(__DIR__,BARTLEBY_ROOT_FOLDER);

function logMessage($message=""){
echo ($message."<br>\n");
}
$today = date("Ymd-H:m:s");
logMessage ("Running installer on ".$today);
try {
logMessage("Connecting to MONGO");
$m = new MongoClient();
} catch (Exception $e) {
logMessage("Mongo client must be installed ". $e->getMessage());
}
logMessage("Selecting the database  ".$configuration->get_MONGO_DB_NAME());
$db = $m->selectDB($configuration->get_MONGO_DB_NAME());// Selecting  base

logMessage("Erasing all the collections if necessary");
// Erase all the collections

$collectionList=$db->listCollections();
logMessage("Number of collection ".count($collectionList));
foreach ($collectionList as $collection) {
logMessage("Droping ".$collection->getName());
$collection->drop();
}
logMessage("Recreating the collections");
// Collection creation
<?php
/* @var $d ProjectRepresentation */
/* @var $entity EntityRepresentation */
foreach ($d->entities as $entity ) {
    $name=$entity->name;
    if(isset($prefix)){
        $name=str_replace($prefix,'',$name);
    }
    if (isset($excludeEntitiesWith) && in_array($name,$excludeEntitiesWith)){
        continue;
    }
    $pluralized=lcfirst(Pluralization::pluralize($name));
    echoIndent('logMessage("Creating the '.$pluralized.' collection");'.cr(),0);
    echoIndent('$'.$pluralized.'=$db->createCollection("'.$pluralized.'");'.cr(),0);
}
?>
<?php /*<- END OF TEMPLATE */?>
