<?php

include FLEXIONS_SOURCE_DIR.'/SharedMPS.php';
require_once FLEXIONS_SOURCE_DIR.'helpers/classes/LocalSwiftTools.class.php';




if (isset ( $f )) {
    $f->fileName = LocalSwiftTools::getCurrentClassNameWithPrefix($d).'.swift';
    $f->package = 'iOS/swift/models/';
}

echo "";

?>