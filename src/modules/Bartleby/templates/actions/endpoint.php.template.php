 <?php

require_once FLEXIONS_MODULES_DIR . '/Bartleby/templates/Requires.php';

/* @var $f Flexed */
/* @var $d ActionRepresentation*/

if (isset ( $f )) {
    $classNameWithoutPrefix=ucfirst(substr($d->class,strlen($h->classPrefix)));
    $callDataClassName=$classNameWithoutPrefix.'CallData';
    $f->fileName = $classNameWithoutPrefix.'.php';
    $f->package = 'php/api/'.$h->majorVersionPathSegmentString().'generated/endpoints/';
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

 namespace Bartleby\EndPoints;

 require_once BARTLEBY_ROOT_FOLDER . 'mongo/MongoEndPoint.php';

 use Bartleby\Core\CallData;
 use Bartleby\Core\JsonResponse;
 use Bartleby\Mongo\MongoEndPoint;

 class  <?php echo $callDataClassName; ?> extends CallData {

 }

 class  <?php echo $classNameWithoutPrefix; ?> extends MongoEndPoint {

     function POST(<?php echo $callDataClassName; ?> $parameters) {
        return new JsonResponse(NULL, 200);
     }

     function GET(<?php echo $callDataClassName; ?> $parameters) {
        return new JsonResponse(array('authenticated'=>$this->isAuthenticated()), 200);
     }

     function PUT(<?php echo $callDataClassName; ?> $parameters) {
        return new JsonResponse(NULL, 200);
     }

     function DELETE(<?php echo $callDataClassName; ?> $parameters) {
        return new JsonResponse(NULL, 202);
     }

 }

<?php echo '?>'?><?php /*<- END OF TEMPLATE */?>

