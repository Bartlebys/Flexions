<?php
/**
 * Created by PhpStorm.
 * User: bpds
 * Date: 10/07/15
 * Time: 17:13
 */


if (isset ( $f )) {
    $f->fileName = 'bootstrap.php';
    $f->package = "php/api/";
}

echo('
<?php
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR ."/v1/SLYTrackerConfig.php";
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . "/v1/SLYTrackerConst.php";
require_once dirname ( __FILE__ ) . DIRECTORY_SEPARATOR ."/v1/SLYTrackerAPI.class.php";

try {
    $API = new SLYTrackerAPI ();
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
