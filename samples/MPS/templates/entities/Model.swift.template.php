<?php

include FLEXIONS_SOURCE_DIR.'/variables-for-MPS.php';
require_once FLEXIONS_SOURCE_DIR.'classes/LocalSwiftTools.class.php';




if (isset ( $f )) {
    $f->fileName = LocalSwiftTools::getCurrentClassNameWithPrefix($d).'.swift';
    $f->package = 'iOS/swift/models/';
}

echo "


";

?>