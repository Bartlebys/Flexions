<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 09/07/15
 * Time: 11:29
 */

require_once FLEXIONS_SOURCE_DIR.'/SharedMPS.php';

if (isset ( $f )) {
    $f->fileName = 'destructiveInstaller.php';
    $f->package = "php/tools/";
}

/**
 *
 * GENERATION STARTS HERE !
 *
 */

echo '
<?php
/**
* A destructive installer script for '.$f->projectName .'
*/

function logMessage($message=""){
    echo ($message."\n");
}

$today = date("Ymd-H:m:s");
logMessage ("Running installer on ".$today);

try {
    logMessage("Connecting to ".DB_NAME);
    $m = new MongoClient();

} catch (Exception $e) {
    logMessage("Mongo client must be installed ". $e->getMessage());
}
logMessage("Connected  to ".DB_NAME);


$db = $m->selectDB(DB_NAME);// Selecting  base

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
';

/* @var $entity EntityRepresentation */
foreach ($d as $entity ) {
    $pluralized=strtolower(Pluralization::pluralize($entity->name));
    echoIndent('logMessage("Creating the '.$pluralized.' collection");'.cr(),0);
    echoIndent('$'.$pluralized.'=$db->createCollection("'.$pluralized.'");'.cr(),0);
}
