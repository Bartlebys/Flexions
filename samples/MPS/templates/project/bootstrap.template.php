<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */

require_once FLEXIONS_SOURCE_DIR.'/SharedMPS.php';

if (isset ( $f )) {
    $f->fileName = 'bootstrap.php';
    $f->package = "php/api/";
}

echo('<?php
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR ."/v1/Config.php";
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . "/v1/Const.php";
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR ."/v1/Api.class.php";

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
}');

