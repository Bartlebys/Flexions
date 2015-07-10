<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 09/07/15
 * Time: 11:29
 */

require_once FLEXIONS_ROOT_DIR . 'modules/Utils/Pluralization.php';

if (isset ( $f )) {
    $f->fileName = 'DestructiveInstaller.php';
    $f->package = "tools/";
    $f->projectName = "MPS-Sample";
    $f->license = FLEXIONS_ROOT_DIR."flexions/helpers/licenses/LGPL.tpl.php";
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

try {
    $m = new MongoClient();
} catch (Exception $e) {
    logMessage("Mongo client must be installed ". $e->getMessage());
}
logMessage("Connecting to ".DB_NAME);
$db = $m->selectDB(DB_NAME);// Selecting the mongo database

logMessage("Creating the messages collection");
$messages=$db->createCollection("messages");
logMessage("Insuring Geo index on messages.2dloc");
$messages->createIndex( array("location"=> "2dsphere" ) );

';

