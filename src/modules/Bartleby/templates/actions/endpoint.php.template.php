<?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation*/

if (isset ( $f )) {
    $classNameWithoutPrefix=$h->ucFirstRemovePrefixFromString($d->class);
    $callDataClassName=$classNameWithoutPrefix.'CallData';
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'generated/endpoints/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

 namespace Bartleby\EndPoints;

 require_once BARTLEBY_ROOT_FOLDER . 'mongo/MongoEndPoint.php';

 use Bartleby\Core\CallDataRawWrapper;
 use Bartleby\Core\JsonResponse;
 use Bartleby\Mongo\MongoEndPoint;

class  <?php echo $callDataClassName; ?> extends CallDataRawWrapper {
// WRAPPING PROP NAME
    }

 class  <?php echo $classNameWithoutPrefix; ?> extends MongoEndPoint {
<?php
if($d->httpMethod=='POST') {
    echo('
     function call('.$callDataClassName.' $parameters) {
        return new JsonResponse(NULL, 200);
     }');
}elseif ($d->httpMethod=='GET'){
    echo('
     function call('.$callDataClassName.' $parameters) {
        $db=$this->getDb();
        $collection = $db->{'.$d->collectionName.'};
        try {
            $q = array (
                \'_id\' =>$parameters->id
            );
            $obj = $collection->findOne ( $q );
            if (isset ( $user )) {
                return new JsonResponse($obj,200);
            } else {
                return new JsonResponse(NULL,404);
            }
        } catch ( MongoCursorException $e ) {
            return new JsonResponse(\'MongoCursorException\' . $e->getCode() . \' \' . $e->getMessage(), 417);
        }

        return new JsonResponse(NULL, 200);
     }');
}elseif ($d->httpMethod=='PUT'){
    echo('
     function call('.$callDataClassName.' $parameters) {
        return new JsonResponse(NULL, 200);
     }'
    );
}elseif ($d->httpMethod=='DELETE'){
    // DELETE
    echo('
     function call('.$callDataClassName.' $parameters) {
        return new JsonResponse(NULL, 200);
     }'
    );
}else{
    echo('// STRANGE METHOD '.$d->httpMethod);
}
?>

 }

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>