<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

require_once FLEXIONS_SOURCE_DIR.'/SharedSwagger.php';
/* @var $f Flexed */


if (isset ( $f )) {
    $f->fileName = 'bootstrap.php';
    $f->package = "php/api/";
}

/* TEMPLATES STARTS HERE -> */?>
<?php echo '<?php'?>
<?php echo GenerativeHelperForPhp::defaultHeader($f,$d); ?>

require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR ."<?php echo $h->majorVersionPathSegmentString() ?>Config.php";
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR ."<?php echo $h->majorVersionPathSegmentString() ?>Const.php";
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR ."<?php echo $h->majorVersionPathSegmentString() ?>Api.class.php";

try {
    $API = new API ();
    echo $API->run ();
} catch ( Exception $e ) {
    $status=500;
    $header = "HTTP/1.1 " . $status . " " . $API->requestStatus ( $status );
    header ( $header );
    echo json_encode ( Array (
        "description"=>$API->errorDescription,
        "error" => $e->getMessage ()
    ) );
}
<?php /*<- END OF TEMPLATE */?>